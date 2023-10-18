<?php

namespace App\Http\Controllers\erpNet\Timbrado;

use App\Http\Controllers\Controller;
use App\Mail\EnviarCorreo;
use App\Models\catalogos\CAT_ARTICLES;
use App\Models\catalogos\CAT_COMPANIES;
use App\Models\catalogosSAT\CAT_SAT_FORMAPAGO;
use App\Models\CatalogosSAT\CAT_SAT_REGIMENFISCAL;
use App\Models\catalogosSAT\CAT_SAT_USOCFDI;
use PDF;
use Illuminate\Support\Facades\Mail;
use QrCode;
use App\Models\catalogos\CAT_CUSTOMERS;
use App\Models\catalogos\CONF_CREDIT_CONDITIONS;
use App\Models\catalogos\CONF_FORMS_OF_PAYMENT;
use App\Models\catalogos\CONF_GENERAL_PARAMETERS;
use App\Models\catalogos\CONF_MODULES_CONCEPT;
use App\Models\catalogos\CONF_MONEY;
use App\Models\catalogos\CONF_UNITS;
use App\Models\catalogosSAT\CAT_SAT_CLAVEPEDIMENTO;
use App\Models\catalogosSAT\CAT_SAT_METODOPAGO;
use App\Models\catalogosSAT\CAT_SAT_MOTIVO_TRASLADO;
use App\Models\catalogosSAT\CAT_SAT_TIPOOPERACION;
use App\Models\catalogosSAT\CAT_SAT_UNIDAD_MEDIDA;
use App\Models\historicos\HIST_STAMPED;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_DETAILS;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_FILES;
use App\Models\modulos\PROC_CANCELED_CFDI;
use App\Models\modulos\PROC_CANCELED_REFERENCE;
use App\Models\modulos\PROC_DEL_SERIES_MOV2;
use App\Models\modulos\PROC_KIT_ARTICLES;
use App\Models\modulos\PROC_SALES;
use App\Models\modulos\PROC_SALES_DETAILS;
use App\Models\modulos\PROC_SALES_FILES;
use App\Models\modulos\PROC_SALES_FOREIGN_TRADE;
use App\Models\modulos\PROC_SALES_PAYMENT;
use App\Models\timbrado\PROC_CANCELED_REASON;
use App\Models\timbrado\PROC_CFDI;
use App\Models\timbrado\PROC_CFDI_CXC_REFERENCE;
use App\Models\timbrado\PROC_CFDI_REFERENCE;
use Carbon\Carbon;
use DateTimeImmutable;
use DOMDocument;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class TimbradoController extends Controller
{

    private $mensaje;
    private $status;
    private $statusConexion;

    public function timbrarFactura($idVenta, $request)
    {
        try {
            //Obtenemos la informacion de la empresa
            $empresa = session('company');


            if ($empresa->companies_routeCertificate == null || $empresa->companies_routeKey == null) {
                $this->setMensaje('Error al timbrar: Error al obtener los archivos de SAT de la empresa');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }

            //hacemos otra validación por si los archivos del sat no terminan con .cer o .key
            $extensionCer = explode(".", $empresa->companies_routeCertificate);
            $extensionKey = explode(".", $empresa->companies_routeKey);

            if ($extensionCer[1] !== "cer" || $extensionKey[1] !== "key") {
                $this->setMensaje('Error al timbrar: Verifique que los archivos del SAT de la empresa sean .cer y .key');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }

            $rutaCer = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeCertificate));
            $rutaKey = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeKey));


            //Sacar los datos de la venta
            $venta = PROC_SALES::where('sales_id', '=', $idVenta)->first();
            $ventaComercioExterior = PROC_SALES_FOREIGN_TRADE::WHERE("salesForeingTrade_saleID", '=', $venta->sales_id)->first();
            $articulosVenta = PROC_SALES_DETAILS::where('salesDetails_saleID', '=', $idVenta)
                ->join('CAT_ARTICLES', 'CAT_ARTICLES.articles_key', '=', 'PROC_SALES_DETAILS.salesDetails_article')
                ->get();
            $cliente = CAT_CUSTOMERS::where('customers_key', '=', $venta->sales_customer)->first();
            $condicionCredito = CONF_CREDIT_CONDITIONS::where('creditConditions_id', '=', $venta->sales_condition)->first();
            // dd($condicionCredito);

            $formaPagoVenta = PROC_SALES_PAYMENT::where('salesPayment_saleID', '=', $idVenta)->first();
            $moneda = CONF_MONEY::where('money_key', '=', $venta->sales_money)->first();
            $metodoPago = CAT_SAT_METODOPAGO::where('descripcion', $condicionCredito->creditConditions_paymentMethod)->first();

            if ($formaPagoVenta) {
                if ($formaPagoVenta->salesPayment_paymentMethod1 !== "") {
                    $formaPago1 = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $formaPagoVenta->salesPayment_paymentMethod1)->select('formsPayment_sat')->first();
                }
                if ($formaPagoVenta->salesPayment_paymentMethod2 !== "") {
                    $formaPago2 = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $formaPagoVenta->salesPayment_paymentMethod2)->select('formsPayment_sat')->first();
                }
                if ($formaPagoVenta->salesPayment_paymentMethod3 !== "") {
                    $formaPago3 = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $formaPagoVenta->salesPayment_paymentMethod3)->select('formsPayment_sat')->first();
                }
            }


            //generar el xml para facturacion
            $certificado = new \CfdiUtils\Certificado\Certificado($rutaCer);

            //Creamos el archivo donde guardaremos la llave privada
            $rutaBase = $empresa->companies_routeFiles . "CFDI/llaveprivada.key.pem";
            $urlArchivoTemporal = str_replace('/', '\\', Storage::path("empresas/" . $rutaBase));
            $keyDerPass = trim(Crypt::decrypt($empresa->companies_passwordKey));
            // $openssl = new \CfdiUtils\OpenSSL\OpenSSL();



            //Validamos si existe el archivo llaveprivada.key.pem
            if (Storage::disk('empresas')->exists($rutaBase)) {
                //Si existe lo eliminamos
                Storage::disk('empresas')->delete($rutaBase);
            }

            //Otra manera de desencrptar el archivo key de la empresa
            exec('openssl pkcs8 -inform DER -in ' . $rutaKey . ' -passin pass:' . $keyDerPass . ' -out ' . $urlArchivoTemporal, $result, $status);
            if ($status === 1) {
                $this->setMensaje('Error al timbrar: Error al obtener la llave privada desencriptada');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }
            // $openssl->derKeyConvert($rutaKey, $keyDerPass, $urlArchivoTemporal);

            //Buscamos el archivo llaveprivada.key.pem
            $llavePrivadaPem = Storage::disk('empresas')->get($rutaBase);

            //Formamos el array de la forma de pago y metodo de pago
            $arrayFormaPago = [];

            if ($metodoPago->c_MetodoPago === "PPD") {
                $arrayFormaPago = [
                    "MetodoPago" => $metodoPago->c_MetodoPago,
                    "FormaPago" => "99"
                ];
            } else {
                $formaPagoPrincipal = "";
                $contador = 0;
                if (isset($formaPago1)) {
                    $formaPagoPrincipal = $formaPago1->formsPayment_sat;
                    $contador++;
                }
                if (isset($formaPago2)) {
                    $formaPagoPrincipal = $formaPago2->formsPayment_sat;
                    $contador++;
                }
                if (isset($formaPago3)) {
                    $formaPagoPrincipal = $formaPago3->formsPayment_sat;
                    $contador++;
                }

                if ($contador > 1) {
                    $formaPagoPrincipal = "99";
                }

                $arrayFormaPago = [
                    "MetodoPago" => $metodoPago->c_MetodoPago,
                    "FormaPago" => $formaPagoPrincipal,
                ];
            }


            $atributosCFDI = [
                'LugarExpedicion' => $empresa->companies_cp,
                'TipoDeComprobante' => 'I',
                'Moneda' => $moneda->money_keySat,
                'TipoCambio' => intval($moneda->money_change),
                'Fecha' => Carbon::now()->format('Y-m-d\TH:i:s'),
                'Folio' => $venta->sales_movementID,
            ];
            // dd($atributosCFDI);

            if ($ventaComercioExterior !== null) {
                $tipoExportacion = [
                    'Exportacion' => '02',
                ];
            } else {
                $tipoExportacion = [
                    'Exportacion' => '01',
                ];
            }

            $atributos = array_merge($atributosCFDI, $tipoExportacion);
            $comprobanteAtributos = array_merge($atributos, $arrayFormaPago);


            // dd($comprobanteAtributos);

            $creator = new \CfdiUtils\CfdiCreator40($comprobanteAtributos, $certificado);
            $comprobante = $creator->comprobante();

            // dd($venta,$articulosVenta, $cliente, $condicionCredito, $metodoPago, $empresa, $moneda, $comprobanteAtributos, $certificado);

            $comprobante->addEmisor([
                'Rfc' => trim($empresa->companies_rfc),
                'Nombre' => trim($empresa->companies_name),
                'RegimenFiscal' => $empresa->companies_taxRegime,
            ]);


            $informacionReceptor = [
                'Rfc' => trim($cliente->customers_RFC),
                'Nombre' => trim($cliente->customers_businessName),
                'UsoCFDI' => $cliente->customers_identificationCFDI,
                'RegimenFiscalReceptor' => $cliente->customers_taxRegime,
                'DomicilioFiscalReceptor' => $cliente->customers_cp,
            ];

            if ($ventaComercioExterior !== null) {
                $informacionReceptor = array_merge($informacionReceptor, [
                    'ResidenciaFiscal' => $cliente->customers_country,
                    'NumRegIdTrib' =>  $cliente->customers_numRegIdTrib,
                ]);
            }

            $comprobante->addReceptor($informacionReceptor);


            //Validamos en caso de que sea una factura con relación
            $dataFacturaInfo = json_decode($request->dataFacturaInfo, true);

            if (!$dataFacturaInfo['Normal']) {
                $facturaRelacion = PROC_CFDI::WHERE('cfdi_module', '=', 'Ventas')->WHERE('cfdi_moduleID', '=', $dataFacturaInfo['facturaRelacion'])->first();
                $comprobante->addCfdiRelacionados(['TipoRelacion' => '04'])->addCfdiRelacionado([
                    'UUID' => $facturaRelacion->cfdi_UUID
                ]);
            }
            //Validamos si el movimiento tiene informacion de comercio exterior
            if ($ventaComercioExterior !== null) {
                //Agregamos los nodos del CFDI para comercio exterior
                $attrCE = [
                    'Version' => "1.1",
                    'TipoOperacion' => $ventaComercioExterior->salesForeingTrade_operationType,
                    'ClaveDePedimento' => $ventaComercioExterior->salesForeingTrade_petitionKey,
                    'CertificadoOrigen' => $ventaComercioExterior->salesForeingTrade_certificateOforigin,
                    'Subdivision' => $ventaComercioExterior->salesForeingTrade_subdivision,
                    'Incoterm' => $ventaComercioExterior->salesForeingTrade_incoterm,
                    'TipoCambioUSD' => $moneda->money_change,
                    'TotalUSD' => number_format($venta->sales_total, 2, '.', ''),
                ];

                if ($ventaComercioExterior->salesForeingTrade_certificateOforigin !== '0') {
                    $attrCE = array_merge($attrCE, [
                        'NumCertificadoOrigen' => $ventaComercioExterior->salesForeingTrade_numberCertificateOrigin,
                    ]);
                }

                if ($ventaComercioExterior->salesForeingTrade_subdivision !== '0') {
                    $attrCE = array_merge($attrCE, [
                        'NumeroExportadorConfiable' => $ventaComercioExterior->salesForeingTrade_salesForeingTrade_trustedExportedNumber,
                    ]);
                }

                if ($ventaComercioExterior->salesForeingTrade_transferReason !== "") {
                    $attrCE = array_merge($attrCE, [
                        'MotivoTraslado' => $ventaComercioExterior->salesForeingTrade_transferReason,
                    ]);
                }

                $comercioExterior = new \CfdiUtils\Elements\Cce11\ComercioExterior($attrCE);

                //Llenamos los children para el comercio exterior Emisor, Receptor y Mercancias
                $tipoRegimenCurp = [];

                if ($empresa->companies_taxRegime === "612") {
                    $tipoRegimenCurp = [
                        'Curp' => $empresa->companies_employerRegistration,
                    ];
                }

                $paisEmpresa = explode("-", $empresa->companies_country)[1];
                $municipioEmpresa = explode("-", $empresa->companies_city)[1];
                $coloniaEmpresa = trim(explode("-", $empresa->companies_suburb)[1]);
                $coloniaCliente = trim(explode("-", $cliente->customers_colonyFractionation)[1]);
                $municipioCliente = explode("-", $cliente->customers_townMunicipality)[0];

                $comercioExterior->addEmisor($tipoRegimenCurp)->addDomicilio([
                    'Calle' => $empresa->companies_addres,
                    'Colonia' =>   $coloniaEmpresa,
                    'Municipio' => $municipioEmpresa,
                    'Estado' => explode("-", $empresa->companies_state)[1],
                    'Pais' => $paisEmpresa,
                    'CodigoPostal' => $empresa->companies_cp,
                ]);

                // dd($coloniaCliente, $municipioCliente, $coloniaEmpresa, $municipioEmpresa);

                $comercioExterior->addReceptor([
                    'NumRegIdTrib' => $cliente->customers_numRegIdTrib,
                ])->addDomicilio([
                    "Calle" => $cliente->customers_addres,
                    "NumeroExterior" => $cliente->customers_interiorNumber,
                    "Colonia" =>  $coloniaCliente,
                    "Estado" => $cliente->customers_state,
                    "Pais" => $cliente->customers_country,
                    "CodigoPostal" => $cliente->customers_cp,
                    'Municipio' => $municipioCliente
                ]);

                // dd($cliente->customers_numRegIdTrib);



                $mercancias = $comercioExterior->addMercancias();
            }

            foreach ($articulosVenta as $key => $articuloVenta) {
                $articulo = CAT_ARTICLES::where('articles_key', $articuloVenta->salesDetails_article)->first();
                $unidad = CONF_UNITS::WHERE('units_unit', $articuloVenta->salesDetails_unit)->select("units_keySat")->first();
                $prodServ = [];
                $objectTax = [];
                //Validamos si tiene añadida las retenciones el articulo

                if ($articulo->articles_productService !== null) {
                    $prodServ = [
                        'ClaveProdServ' => explode("-", $articulo->articles_productService)[0],
                    ];
                }

                if ($articulo->articles_objectTax !== null) {
                    $objectTax  = [
                        'ObjetoImp' => $articulo->articles_objectTax,
                    ];
                }

                $infoArticulo = array_merge($prodServ, $objectTax);

                //Validamos el descuento
                if ($articuloVenta->salesDetails_discount == null || $articuloVenta->salesDetails_discount == 0.00) {
                    $descuento = [];
                } else {
                    $descuento = [
                        'Descuento' => number_format($articuloVenta->salesDetails_discount, 2, '.', ''),
                    ];
                }

                $conceptos = array_merge([
                    'Cantidad' => $articuloVenta->salesDetails_quantity,
                    'ClaveUnidad' => $unidad->units_keySat,
                    'Unidad' => $articuloVenta->salesDetails_unit,
                    'ClaveProdServ' => explode("-", $articulo->articles_productService)[0],
                    'NoIdentificacion' => $articuloVenta->salesDetails_article,
                    'Descripcion' => $articuloVenta->salesDetails_descript,
                    'ValorUnitario' => number_format($articuloVenta->salesDetails_unitCost, 2, '.', ''),
                    'Importe' => number_format($articuloVenta->salesDetails_amount, 2, '.', ''),
                    'Unidad' => $articuloVenta->salesDetails_unit,
                ], $descuento);

                $conceptosFinales = array_merge($conceptos, $infoArticulo);

                if ($articulo->articles_objectTax === "02" || ($articulo->articles_objectTax === "03" && $articulo->articles_porcentIva !== null)) {
                    $trasladoInformacion = [
                        'Base' => number_format($articuloVenta->salesDetails_amount, 2, '.', ''),
                        'Impuesto' => '002',
                        'TipoFactor' => 'Tasa',
                    ];

                    if ($articulo->articles_porcentIva === "0" || $articulo->articles_porcentIva === "0.0" || $articulo->articles_porcentIva === null) {


                        if ($ventaComercioExterior !== null) {
                            $trasladoInformacion['TasaOCuota'] = '0.000000';
                            $trasladoInformacion['Importe'] = 0.00;
                        } else {
                            $trasladoInformacion['TasaOCuota'] = '0.000000';
                            $trasladoInformacion['Importe'] = number_format($articuloVenta->salesDetails_amount, 2, '.', '');
                        }
                    } else {

                        if ($ventaComercioExterior !== null) {
                            $trasladoInformacion['TasaOCuota'] = '0.000000';
                            $trasladoInformacion['Importe'] = 0.00;
                        } else {
                            $trasladoInformacion['TasaOCuota'] = '0.160000';
                            $trasladoInformacion['Importe'] = number_format($articuloVenta->salesDetails_amount * 0.160000, 2, '.', '');
                        }
                    }

                    $retencionesGlobales = $comprobante->addConcepto($conceptosFinales);
                    $retencionesGlobales->addTraslado($trasladoInformacion);

                    if ($articulo->articles_retention1 != null && $articulo->articles_retention1 != 0) {
                        $isr = number_format(floatval($articulo->articles_retention1) / 100, 6, '.', '');
                        $base = number_format($articuloVenta->salesDetails_amount, 2, '.', '');
                        $retencionesGlobales->addRetencion([
                            'TipoFactor' => 'Tasa',
                            'TasaOCuota' => $isr,
                            'Impuesto' => '001',
                            'Base' => $base,
                            'Importe' =>  number_format($base * $isr, 2, '.', ''),
                        ]);
                    }

                    if ($articulo->articles_retention2 != null && $articulo->articles_retention2 != 0) {
                        $iva = number_format(floatval($articulo->articles_retention2) / 100, 6, '.', '');
                        $base = number_format($articuloVenta->salesDetails_amount, 2, '.', '');
                        $retencionesGlobales->addRetencion([
                            'TipoFactor' => 'Tasa',
                            'TasaOCuota' => $iva,
                            'Impuesto' => '002',
                            'Base' => $base,
                            'Importe' =>  number_format($base * $iva, 2, '.', ''),
                        ]);
                    }

                    // if($articulo->articles_type == "Kit"){
                    //     $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articuloVenta->salesDetails_article)->get();

                    //     foreach ($articulosKit as $articuloKit) {

                    //         $articuloInfo = CAT_ARTICLES::where('articles_key', $articuloKit->procKit_articleID)->first();
                    //         $unidad = CONF_UNITS::WHERE('units_id', $articuloInfo->articles_unitSale)->first();
                    //         $infoArticuloKit = [
                    //             'ClaveProdServ' =>  explode("-",$articuloInfo->articles_productService)[0],
                    //             'NoIdentificacion' => $articuloInfo->articles_key,
                    //             'Unidad' => $unidad->units_unit,
                    //             'Cantidad' => number_format($articuloKit->procKit_cantidad, 2, '.', ''),
                    //             'Descripcion' => $articuloInfo->articles_descript,
                    //         ];

                    //         $retencionesGlobales->addParte($infoArticuloKit);

                    //     }
                    // }
                } else {
                    $retencionesGlobales =  $comprobante->addConcepto($conceptosFinales);
                }

                if ($articulo->articles_type == "Kit") {
                    $articulosKit = PROC_KIT_ARTICLES::where('procKit_article', $articuloVenta->salesDetails_article)->where('procKit_saleID', $venta->sales_id)->where('procKit_affected', 1)->get();

                    foreach ($articulosKit as $articuloKit) {

                        $articuloInfo = CAT_ARTICLES::where('articles_key', $articuloKit->procKit_articleID)->first();
                        $unidad = CONF_UNITS::WHERE('units_id', $articuloInfo->articles_unitSale)->first();
                        $infoArticuloKit = [
                            'ClaveProdServ' =>  explode("-", $articuloInfo->articles_productService)[0],
                            'NoIdentificacion' => $articuloInfo->articles_key,
                            'Unidad' => $unidad->units_unit,
                            'Cantidad' => number_format($articuloKit->procKit_cantidad, 0, '.', ''),
                            'Descripcion' => $articuloInfo->articles_descript,
                        ];

                        $retencionesGlobales->addParte($infoArticuloKit);
                    }
                }

                if ($ventaComercioExterior !== null) {
                    if (isset($articulo->articles_descript2)) {
                        $marca = $articulo->articles_descript2;
                    } else {
                        $marca = 'SIN MARCA';
                    }

                    $mercancias->addMercancia([
                        'NoIdentificacion' => $articuloVenta->salesDetails_article,
                        'FraccionArancelaria' =>  explode("-", $articulo->articles_tariffFraction)[0],
                        'UnidadAduana' => explode("-", $articulo->articles_customsUnit)[0],
                        'CantidadAduana' => number_format($articuloVenta->salesDetails_quantity, 2, '.', ''),
                        'ValorUnitarioAduana' => number_format($articuloVenta->salesDetails_unitCost, 2, '.', ''),
                        'ValorDolares' => number_format($articuloVenta->salesDetails_total, 2, '.', ''),
                    ])->addDescripcionesEspecificas([
                        'Marca' =>  $marca,
                    ]);
                }
            }

            if ($ventaComercioExterior !== null) {
                $comprobante->addComplemento($comercioExterior);
            }

            // // método de ayuda para establecer las sumas del comprobante e impuestos
            // // con base en la suma de los conceptos y la agrupación de sus impuestos
            $creator->addSumasConceptos(null, 2);

            // // método de ayuda para generar el sello (obtener la cadena de origen y firmar con la llave privada
            $creator->addSello($llavePrivadaPem);

            // // método de ayuda para mover las declaraciones de espacios de nombre al nodo raíz
            $creator->moveSatDefinitionsToComprobante();

            // // método de ayuda para validar usando las validaciones estándar de creación de la librería
            $asserts = $creator->validate();
            $asserts->hasErrors(); // true o false

            // método de ayuda para generar el xml y guardar los contenidos en un archivo
            // $creator->saveXml('C:\inetpub\wwwroot\meinsur\storage\app\empresas\Ruta\CFDI\cfdi-sin-Timbrar.xml');

            // // método de ayuda para generar el xml y retornarlo como un string
            $xml = $creator->asXml();

            //  dd($xml);
            // if ($cliente->customers_mail1 !== null && $cliente->customers_mail2 !== null) {
            //     $mailCliente = [$cliente->customers_mail1, $cliente->customers_mail2];
            // } else if ($cliente->customers_mail1 !== null && $cliente->customers_mail2 === null) {
            //     $mailCliente = [$cliente->customers_mail1];
            // } else if ($cliente->customers_mail1 === null && $cliente->customers_mail2 !== null) {
            //     $mailCliente = [$cliente->customers_mail2];
            // } else {
            //     $mailCliente = [];
            // }
            $mailCliente = [$cliente->customers_mail1, $cliente->customers_mail2];
            if ($cliente->customers_mail1 !== null && $cliente->customers_mail2 !== null) {
                $mailCliente = [$cliente->customers_mail1, $cliente->customers_mail2];
            } else if ($cliente->customers_mail1 !== null && $cliente->customers_mail2 === null) {
                $mailCliente = [$cliente->customers_mail1];
            } else if ($cliente->customers_mail1 === null && $cliente->customers_mail2 !== null) {
                $mailCliente = [$cliente->customers_mail2];
            } else {
                $mailCliente = [];
            }


            // if($cliente->customers_mail1 !== null){
            //     $mailCliente = $cliente->customers_mail1;
            // }else if($cliente->customers_mail2 !== null){
            //     $mailCliente = $cliente->customers_mail2;
            // }else{
            //     dd('Necesitamos un correo valido del cliente para enviar los xml y facturas');
            // }
            //Validamos si existe el archivo llaveprivada.key.pem
            if (Storage::disk('empresas')->exists($rutaBase)) {
                //Si existe lo eliminamos
                Storage::disk('empresas')->delete($rutaBase);
            }



            $this->facturacion($xml, $cliente, $venta, $mailCliente, $request);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    //Enviamos el xml facturado al servicio PROC ADVANCE
    public function facturacion($xml, $cliente, $venta, $mailCliente, $request)
    {
        $cfdi = $xml;
        $API_KEY = env('PAC_ADVANCE_KEY');
        $API_WEB = env('PAC_ADVANCE_WEB');
        // dd($API_KEY, $API_WEB);
        try {
            //Configuramos el cliente soap
            $client = new \SoapClient($API_WEB);

            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

            if ($parametro === null || $parametro->generalParameters_filesCustomers === Null || $parametro->generalParameters_filesCustomers === '') {
                $empresaRuta = session('company')->companies_routeFiles . 'Clientes';
            } else {
                $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesCustomers;
            }

            //si ocurre un error en el cliente soap
            $err = $client->__getLastResponse();
            if ($err) {
                dd($err);
            }

            //ejecutamos la llamada al metodo
            $result = $client->__soapCall('timbrar2', array('credential' => $API_KEY, 'cfdi' => $cfdi,));


            //actualizamos el xml con el timbre

            if ($result->Code === "307" || $result->Code === "200") {
                //formamos la ruta del xml timbrado
                $año = Carbon::now()->year;
                $mes = Carbon::now()->month;
                $urlXmlFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $cliente->customers_key . '/CFDI/' . $año . '/' . $mes . '/factura-folio-' . $venta->sales_movementID . '.xml');
                //guardamos el xml con el timbre
                $xmlTimbrado = new SimpleXMLElement($result->CFDI);
                $timbraFactura = PROC_SALES::WHERE("sales_id", "=", $venta->sales_id)->first();
                $timbraFactura->sales_stamped = 1;
                $timbraFactura->update();

                $facturaOrigen = PROC_SALES::WHERE("sales_id", "=", $venta->sales_id)->first();
                $facturaCxC = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_origin', '=', $facturaOrigen->sales_movement)->WHERE('accountsReceivable_originID', '=', $facturaOrigen->sales_movementID)->WHERE('accountsReceivable_branchOffice', '=', $facturaOrigen->sales_branchOffice)->WHERE('accountsReceivable_company', '=', $facturaOrigen->sales_company)->first();
                if ($facturaCxC != null) {
                    $facturaCxC->accountsReceivable_stamped = 1;
                    $facturaCxC->update();
                }

                //Validamos en caso de que sea una factura con relación
                $dataFacturaInfo = json_decode($request->dataFacturaInfo, true);
                $canceledReference = new PROC_CANCELED_REFERENCE();

                if (!$dataFacturaInfo['Normal']) {
                    $canceledReference->canceledReference_module = 'Ventas';
                    $canceledReference->canceledReference_moduleID = $venta->sales_id;
                    $canceledReference->canceledReference_moduleCanceledID = $dataFacturaInfo['facturaRelacion'];
                    $canceledReference->save();
                } else {
                    $canceledReference->canceledReference_module = 'Ventas';
                    $canceledReference->canceledReference_moduleID = $venta->sales_id;
                    $canceledReference->save();
                }

                Storage::disk('empresas')->put($urlXmlFinal, $xmlTimbrado->asXML());
                // $xmlTimbrado->saveXml('C:\inetpub\wwwroot\meinsur\storage\app\empresas\Ruta\CFDI\cfdi2.xml');
                $this->generarPDF($cliente, $urlXmlFinal, $venta, $mailCliente);
                $this->setMensaje('Movimiento Timbrado Correctamente');
                $this->setStatus(true);
                $this->setStatus2(true);
            } else {
                // dd($result);
                $this->setMensaje('Error al timbrar: ' . $result->Message);
                $this->setStatus(false);
                $this->setStatus2(true);
            }
        } catch (\SoapFault $fault) {
            // trigger_error("SOAP Fault: (faultcode: " . $fault->faultcode . ", faultstring: " . $fault->faultstring . ")", E_USER_ERROR);
            $this->setMensaje('Error al timbrar: ' . $fault->faultstring . ', ' . $fault->faultcode);
            //devolvemos el error
            $this->setStatus2(false);
        } catch (\Exception $e) {
            // trigger_error("SOAP Error: " . $e->getMessage(), E_USER_ERROR);
            $this->setMensaje('Error al timbrar: ' . $e->getMessage());
            //devolvemos el error
            $this->setStatus2(false);
        }
    }

    public function generarPDF($cliente, $rutaCFDI, $venta, $mailCliente)
    {
        $CFDI_XML = Storage::disk('empresas')->get($rutaCFDI);
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        if ($parametro === null || $parametro->generalParameters_filesCustomers === Null || $parametro->generalParameters_filesCustomers === '') {
            $empresaRuta = session('company')->companies_routeFiles . 'Clientes';
        } else {
            $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesCustomers;
        }

        $id = $cliente->customers_key; //poner parametro a la funcion
        $XML = \CfdiUtils\Cfdi::newFromString($CFDI_XML)->getQuickReader(); //poner ruta del xml timbrado en el parametro de la funcion}

        $impuestos = $XML->Impuestos;

        $retenciones = $XML->Conceptos->Concepto->Impuestos->Retenciones;
        $traslados = $XML->Conceptos->Concepto->Impuestos->Traslados;
        // dd($retenciones, $traslados);
        $retenciones2 = $XML->Impuestos;
        // dd($traslados);

        $nombreComprobante = 'CFDI de Ingreso';


        // dd($retenciones2);
        //Obtenemos la direccion de la empresa
        $empresa = session('company');
        // dd($empresa);
        $direccion = $empresa->companies_addres . ', CP:' . $empresa->companies_cp . ',' . $empresa->companies_country . ',' . $empresa->companies_state . '-' . $empresa->companies_suburb;

        //Obtenemos la direccion del cliente
        $colonia = explode("-", $cliente->customers_colonyFractionation)[0];
        // dd($colonia);
        $direccionCliente = $cliente->customers_addres . ' ' . $cliente->customers_roads . ' Exterior: ' . $cliente->customers_outdoorNumber . ' Interior: ' . $cliente->customers_interiorNumber . ' Col. ' . $colonia . ', CP: ' . $cliente->customers_cp . ', ' . $cliente->customers_country . ', ' . $cliente->customers_state;

        if (isset($impuestos['TotalImpuestosTrasladados'])) {
            $impuestos = $impuestos['TotalImpuestosTrasladados'];
        } else {
            $impuestos = 0;
        }

        $impuestosTotales = $XML->Conceptos->Concepto->Impuestos->Traslados->Traslado['Importe'];
        // dd($impuestosTotales);

        $retencionesTotales = $XML->Conceptos->Concepto->Impuestos->Retenciones->Retencion['Importe'];
        //declaramos la variable de retenciones
        $retencionesChildren = [];

        // dd($retencionesTotales, $impuestosTotales);

        if (isset($retenciones2['TotalImpuestosTrasladados'])) {
            $impuestosTotales = $retenciones2['TotalImpuestosTrasladados'];
            foreach ($traslados() as $retencion) {
                $retencionesChildren[] = $retencion;
            }
        } else {
            $impuestosTotales =  null;
            $retencionesChildren = null;
        }
        // dd($retencionesChildren);



        if (isset($retenciones2['TotalImpuestosRetenidos'])) {
            $retencionesTotales = $retenciones2['TotalImpuestosRetenidos'];
            // dd($retencionesTotales);
            foreach ($retenciones() as $retencion) {
                // dd($retenciones);
                $retencionesChildren[] = $retencion;
            }
            // dd($retenciones);
        } else {
            $retencionesTotales =  null;
            $retencionesChildren = null;
        }

        // dd($retencionesChildren);



        // dd($XML);
        //sacar al emisor
        $emisor = $XML->Emisor;

        $regimenEmisor = CAT_SAT_REGIMENFISCAL::where('c_RegimenFiscal', $emisor['RegimenFiscal'])->first();

        //sacar al receptor
        $receptor = $XML->Receptor;

        $regimenReceptor = CAT_SAT_REGIMENFISCAL::where('c_RegimenFiscal', $receptor['RegimenFiscalReceptor'])->first();

        //sacamos los conceptos
        $conceptos = $XML->Conceptos;


        $existePartes = false;



        foreach ($conceptos() as $concepto) {
            $conceptosArray[] = $concepto;

            // dd($concepto());
            //sacamos las partes
            foreach ($concepto() as $key => $hijos) {
                if ($key != 0) {
                    $existePartes = true;
                }
            }
        }

        $conceptosImpuestos = $XML->Conceptos;
        // dd($conceptosImpuestos);

        //tenemos que hacer una condicional ya que si $existePartes es true tendremos que hacer un foreach de más, sino, lo dejamos como está
        foreach ($conceptosImpuestos() as $concepto) {
            foreach ($concepto() as $key => $hijos) {
                foreach ($hijos() as $key => $hijo) {
                    foreach ($hijo() as $key => $hijo2) {
                        $conceptosImpuestosArray[] = $hijo2;
                    }
                }
            }
        }
        // dd($conceptosImpuestosArray);

        //a los conceptos le agregamos un nodo solo para el PDF llamado Serie
        // $ventaSeries = PROC_SALES::JOIN('PROC_SALES_DETAILS', 'PROC_SALES_DETAILS.salesDetails_saleID', '=', 'PROC_SALES.sales_id')
        //     ->join('PROC_DEL_SERIES_MOV2', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_saleID', '=', 'PROC_SALES_DETAILS.salesDetails_saleID')
        //     ->WHERE('PROC_SALES.sales_id', '=', $venta->sales_id)
        //     ->WHERE('PROC_SALES_DETAILS.salesDetails_type', '=', 'Serie')
        //     ->select('PROC_SALES.sales_id', 'PROC_SALES.sales_movement', 'PROC_SALES.sales_movementID', 'PROC_DEL_SERIES_MOV2.delSeriesMov2_lotSerie', 'PROC_SALES_DETAILS.salesDetails_article', 'PROC_SALES_DETAILS.salesDetails_type')
        //     ->get();

        $ventaSeries = PROC_DEL_SERIES_MOV2::WHERE('delSeriesMov2_saleID', '=', $venta->sales_id)->GET()->pluck('delSeriesMov2_lotSerie', 'delSeriesMov2_article');

        // dd($ventaSeries);
        // dd($ventaSeries);


        //agregamos un nodo adicional de serie a los conceptos

        // $ventaSeries = $ventaSeries->toArray();
        // dd($ventaSeries);
        // dd($XML);
        //moneda del cfdi
        $moneda = $XML['Moneda'];
        //metodo de pago
        $metodoPago = CAT_SAT_METODOPAGO::where('c_MetodoPago', $XML['MetodoPago'])->first();


        $usoCFDI = CAT_SAT_USOCFDI::where('c_UsoCFDI', $receptor['UsoCFDI'])->first();

        //complemento
        $complemento = $XML->Complemento;
        $complementoArray = $complemento->TimbreFiscalDigital;
        $atributosComercioExterior = null;
        $comercioExteriorEmisor = null;
        $comercioExteriorReceptor = null;
        $comercioExteriorMercancias = null;
        $motivosTraslados = [];
        $tipoOperacion = [];
        $claveDePedimento = [];
        $unidArray = [];
        $articlesInfo = [];
        $fraccionesArrancelaria = [];


        if (isset($complemento->ComercioExterior)) {
            $unidadMerca = CAT_SAT_UNIDAD_MEDIDA::all();
            $articulosData = CAT_ARTICLES::all();

            foreach ($unidadMerca as $unidad) {
                $unidArray[$unidad['c_UnidadMedida']] = $unidad['descripcion'];
            }

            foreach ($articulosData as $art) {
                $articlesInfo[$art['articles_key']] = $art;
            }

            $atributosComercioExterior = $complemento->ComercioExterior;
            $comercioExteriorEmisor = $atributosComercioExterior->Emisor;
            $comercioExteriorReceptor = $atributosComercioExterior->Receptor;
            $mercancias = $atributosComercioExterior->Mercancias;

            foreach ($mercancias() as $mercancia) {
                $comercioExteriorMercancias[] = $mercancia;
            }

            if (isset($atributosComercioExterior['MotivoTraslado'])) {
                $motivosTraslados = CAT_SAT_MOTIVO_TRASLADO::WHERE('c_MotivoTraslado', '=', $atributosComercioExterior['MotivoTraslado'])->first();
            }

            if (isset($atributosComercioExterior['TipoOperacion'])) {
                $tipoOperacion = CAT_SAT_TIPOOPERACION::WHERE('c_TipoOperacion', '=', $atributosComercioExterior['TipoOperacion'])->first();
            }

            if (isset($atributosComercioExterior['ClaveDePedimento'])) {
                $claveDePedimento = CAT_SAT_CLAVEPEDIMENTO::WHERE('c_ClavePedimento', '=', $atributosComercioExterior['ClaveDePedimento'])->first();
            }
        }

        //forma de pago
        $formaPago = CAT_SAT_FORMAPAGO::where('c_FormaPago', $XML['FormaPago'])->first();


        //  dd($conceptosArray, $emisor, $receptor, $XML);
        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
        }


        if ($logoFile == null) {
            $logoFile = Storage::disk('empresas')->get('default.png');

            if ($logoFile == null) {
                $logoBase64 = '';
            } else {
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
            }
        } else {
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
        }

        $cfdi = \CfdiUtils\Cfdi::newFromString($CFDI_XML);
        //GENERAR QR
        $parameters = \CfdiUtils\ConsultaCfdiSat\RequestParameters::createFromCfdi($cfdi);
        $qr = $parameters->expression();

        $qrCode = QrCode::size(130)->generate($qr);

        //convertir svg a png
        $qrEncode = "data:image/png;base64," . base64_encode($qrCode);

        $movimientoFactura = [
            'movimiento' => $venta->sales_movement,
            'folioMovimiento' => $venta->sales_movementID,
        ];

        // dd($conceptosArray[0]);

        $pdf = PDF::loadView('include.factura.factura', ['XML' => $XML, 'conceptos' => $conceptosArray, 'emisor' => $emisor, 'receptor' => $receptor, 'regimenEmisor' => $regimenEmisor, 'regimenReceptor' => $regimenReceptor, 'metodoPago' => $metodoPago, 'usoCFDI' => $usoCFDI, 'logo' => $logoBase64, 'complemento' => $complementoArray, 'formaPago' => $formaPago, 'qrEncode' => $qrEncode, 'impuestos' => $impuestos, 'folio' => $venta->sales_movementID, 'direccion' => $direccion, 'direccionCliente' => $direccionCliente, 'moneda' => $moneda, 'atributosComercioExterior' => $atributosComercioExterior, 'comercioExteriorEmisor' =>  $comercioExteriorEmisor, 'comercioExteriorReceptor' =>  $comercioExteriorReceptor, 'comercioExteriorMercancias' =>  $comercioExteriorMercancias, 'motivosTraslados' =>  $motivosTraslados, 'tipoOperacion' => $tipoOperacion, 'claveDePedimento' =>  $claveDePedimento, 'unidArray' =>  $unidArray, 'articulosInfor' =>  $articlesInfo, 'movimientoFactura' => $movimientoFactura, 'retencionesChildren' => $retencionesChildren, 'retencionesTotales' => $retencionesTotales, 'impuestosTotales' => $impuestosTotales, 'existePartes' => $existePartes, 'empresa' => $empresa, 'nombreComprobante' => $nombreComprobante, 'ventaSeries' => $ventaSeries, 'conceptosImpuestosArray' => $conceptosImpuestosArray])->setPaper('A4', 'portrait');



        //guardar el pdf en la ruta del cliente
        $año = Carbon::now()->year;
        $mes = Carbon::now()->month;
        $urlPdfFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $id . '/CFDI/' . $año . '/' . $mes . '/factura-folio-' . $venta->sales_movementID . '.pdf');
        //guardamos el xml con el timbre
        Storage::disk('empresas')->put($urlPdfFinal, $pdf->output());
        //Antes de enviar el correo, guardamos las rutas del CFDI y PDF en la base de datos

        //verificamos q no exista el registro
        $pdfName = 'factura-folio-' . $venta->sales_movementID . '.pdf';
        $xmlName = 'factura-folio-' . $venta->sales_movementID . '.xml';
        $guardarPdfCFDI = PROC_SALES_FILES::WHERE('salesFiles_keySale', '=', $venta->sales_id)->WHERE('salesFiles_file', '=', $pdfName)->first();

        if ($guardarPdfCFDI) {
            $guardarPdfCFDI->salesFiles_keySale = $venta->sales_id;
            $guardarPdfCFDI->salesFiles_path = $urlPdfFinal;
            $guardarPdfCFDI->salesFiles_file = $pdfName;
            $guardarPdfCFDI->update();
        } else {
            $guardarPdfCFDI = new PROC_SALES_FILES();
            $guardarPdfCFDI->salesFiles_keySale = $venta->sales_id;
            $guardarPdfCFDI->salesFiles_path = $urlPdfFinal;
            $guardarPdfCFDI->salesFiles_file = $pdfName;
            $guardarPdfCFDI->save();
        }

        $guardarXmlCFDI = PROC_SALES_FILES::WHERE('salesFiles_keySale', '=', $venta->sales_id)->WHERE('salesFiles_file', '=', $xmlName)->first();

        if ($guardarXmlCFDI) {
            $guardarXmlCFDI->salesFiles_keySale = $venta->sales_id;
            $guardarXmlCFDI->salesFiles_path = $rutaCFDI;
            $guardarXmlCFDI->salesFiles_file = $xmlName;
            $guardarXmlCFDI->update();
        } else {
            $guardarXmlCFDI = new PROC_SALES_FILES();
            $guardarXmlCFDI->salesFiles_keySale = $venta->sales_id;
            $guardarXmlCFDI->salesFiles_path = $rutaCFDI;
            $guardarXmlCFDI->salesFiles_file = $xmlName;
            $guardarXmlCFDI->save();
        }

        //Guardamos los datos correspondientes del CFDI en la base de datos

        $guardarCFDI = PROC_CFDI::WHERE('cfdi_module', '=', "Ventas")->WHERE('cfdi_moduleID', '=', $venta->sales_id)->WHERE('cfdi_movementID', '=', $venta->sales_movementID)->WHERE("cfdi_company", '=', $venta->sales_company)->WHERE("cfdi_branchOffice", '=', $venta->sales_branchOffice)->WHERE("cfdi_cancelled", '=', 0)->first();


        if ($guardarCFDI) {
            $guardarCFDI->cfdi_module = 'Ventas';
            $guardarCFDI->cfdi_moduleID = $venta->sales_id;
            $guardarCFDI->cfdi_movementID = $venta->sales_movementID;
            $guardarCFDI->cfdi_RFC = $XML->Receptor['Rfc'];
            $guardarCFDI->cfdi_amount = $venta->sales_amount;
            $guardarCFDI->cfdi_taxes = $venta->sales_taxes;
            $guardarCFDI->cfdi_total = $venta->sales_total;
            $guardarCFDI->cfdi_certificateNumber = $XML['NoCertificado'];
            $guardarCFDI->cfdi_stamp = $XML['Sello'];
            $guardarCFDI->cfdi_UUID = $XML->Complemento->TimbreFiscalDigital['UUID'];
            $guardarCFDI->cfdi_Path = $rutaCFDI;
            $guardarCFDI->cfdi_stampSat = $XML->Complemento->TimbreFiscalDigital['SelloSAT'];
            $guardarCFDI->cfdi_certificateNumberSat = $XML->Complemento->TimbreFiscalDigital['NoCertificadoSAT'];
            $guardarCFDI->cfdi_cancelled = 0;
            $guardarCFDI->cfdi_year = Carbon::now()->year;
            $guardarCFDI->cfdi_period = Carbon::now()->month;
            $guardarCFDI->cfdi_money = $XML['Moneda'];
            $guardarCFDI->cfdi_typeChange = $XML['TipoCambio'];
            $guardarCFDI->cfdi_company = $venta->sales_company;
            $guardarCFDI->cfdi_branchOffice = $venta->sales_branchOffice;
            $guardarCFDI->cfdi_Pdf = 1;
            $guardarCFDI->cfdi_document = $CFDI_XML;
            $guardarCFDI->update();
        } else {
            $guardarCFDI = new PROC_CFDI();
            $guardarCFDI->cfdi_module = 'Ventas';
            $guardarCFDI->cfdi_moduleID = $venta->sales_id;
            $guardarCFDI->cfdi_movementID = $venta->sales_movementID;
            $guardarCFDI->cfdi_RFC = $XML->Receptor['Rfc'];
            $guardarCFDI->cfdi_amount = $venta->sales_amount;
            $guardarCFDI->cfdi_taxes = $venta->sales_taxes;
            $guardarCFDI->cfdi_total = $venta->sales_total;
            $guardarCFDI->cfdi_certificateNumber = $XML['NoCertificado'];
            $guardarCFDI->cfdi_stamp = $XML['Sello'];
            $guardarCFDI->cfdi_UUID = $XML->Complemento->TimbreFiscalDigital['UUID'];
            $guardarCFDI->cfdi_Path = $rutaCFDI;
            $guardarCFDI->cfdi_stampSat = $XML->Complemento->TimbreFiscalDigital['SelloSAT'];
            $guardarCFDI->cfdi_certificateNumberSat = $XML->Complemento->TimbreFiscalDigital['NoCertificadoSAT'];
            $guardarCFDI->cfdi_cancelled = 0;
            $guardarCFDI->cfdi_year = Carbon::now()->year;
            $guardarCFDI->cfdi_period = Carbon::now()->month;
            $guardarCFDI->cfdi_money = $XML['Moneda'];
            $guardarCFDI->cfdi_typeChange = $XML['TipoCambio'];
            $guardarCFDI->cfdi_company = $venta->sales_company;
            $guardarCFDI->cfdi_branchOffice = $venta->sales_branchOffice;
            $guardarCFDI->cfdi_Pdf = 1;
            $guardarCFDI->cfdi_document = $CFDI_XML;


            $guardarCFDI->save();
        }


        $this->enviarEmail($rutaCFDI, $urlPdfFinal, $cliente, $mailCliente, $venta);
    }

    public function enviarEmail($ruta_xml, $ruta_pdf, $cliente, $mailCliente, $venta)
    {
        // dd($ruta_xml, $ruta_pdf, $cliente, $mailCliente, $venta);
        $nombre = $cliente->customers_businessName;
        $fecha = Carbon::now()->format('d-m-Y');
        //movimiento puede ser sales_movement o accountsReceivable_movement
        $movimiento = $venta->sales_movement ?? $venta->accountsReceivable_movement;
        $email = new EnviarCorreo($ruta_xml, $ruta_pdf, $nombre, $fecha, $movimiento);

        foreach ($mailCliente as $destino) {
            Mail::to($destino)->send($email);
        }
    }

    //Realizamos la cancelacion del CFDI
    public function cancelarFactura($id_venta)
    {
        try {
            $empresa = session('company');
            $API_KEY = env('PAC_ADVANCE_KEY');
            $API_CANCEL_KEY = env('PAC_ADVANCE_WEB_CANCEL');
            //Creamos el archivo donde guardaremos la llave privada
            $rutaCer = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeCertificate));
            $rutaKey = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeKey));
            $rutaBase = $empresa->companies_routeFiles . "CFDI/llaveprivada.key.pem";
            $urlArchivoTemporal = str_replace('/', '\\', Storage::path("empresas/" . $rutaBase));
            $keyDerPass = trim(Crypt::decrypt($empresa->companies_passwordKey));
            // $openssl = new \CfdiUtils\OpenSSL\OpenSSL();



            //Validamos si existe el archivo llaveprivada.key.pem
            if (Storage::disk('empresas')->exists($rutaBase)) {
                //Si existe lo eliminamos
                Storage::disk('empresas')->delete($rutaBase);
            }

            //Otra manera de desencrptar el archivo key de la empresa
            exec('openssl pkcs8 -inform DER -in ' . $rutaKey . ' -passin pass:' . $keyDerPass . ' -out ' . $urlArchivoTemporal, $result, $status);

            if ($status === 1) {
                $this->setMensaje('Error al timbrar: Error al obtener la llave privada desencriptada');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }


            //Buscamos el archivo llaveprivada.key.pem
            $llavePrivadaPem = Storage::disk('empresas')->get($rutaBase);
            $venta = PROC_CFDI::WHERE('cfdi_module', '=', 'Ventas')->WHERE('cfdi_moduleID', '=', $id_venta)->first();
            $motivoCancelaciónVenta = PROC_CANCELED_REASON::WHERE('canceledReason_moduleID', '=', $id_venta)->WHERE('canceledReason_module', '=', 'Ventas')->first();

            $certificado = new \CfdiUtils\Certificado\Certificado($rutaCer);


            $cuerpoInicio = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
              <soap:Body>
              <CancelarRequest>
                  <CancelarRequest>
                    <PrivateKeyPem>' . $llavePrivadaPem . '</PrivateKeyPem>
                    <PublicKeyPem>' . $certificado->getPemContents() . '</PublicKeyPem>
                    <Uuid>' . $venta->cfdi_UUID . '</Uuid>
                    <RfcReceptor>' . $venta->cfdi_RFC . '</RfcReceptor>
                    <Total>' . $venta->cfdi_total . '</Total>
                    <Motivo>' . $motivoCancelaciónVenta->canceledReason_reason . '</Motivo>';
            $cuerpoFinal = '</CancelarRequest>
                </CancelarRequest>
              </soap:Body>
            </soap:Envelope>
            ';

            if ($motivoCancelaciónVenta->canceledReason_reason == "01") {
                $body = $cuerpoInicio . '<FolioSustitucion>' . $motivoCancelaciónVenta->canceledReason_sustitutionUuid . '</FolioSustitucion>' . $cuerpoFinal;
            } else {
                $body = $cuerpoInicio . $cuerpoFinal;
            }

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $API_KEY, 'Cache-Control' => 'no-cache', 'Content-Type' => 'text/xml',
                    'SOAPAction' => 'urn:advans-cfdi-cancelacion#Cancelar',
                ])
                ->withOptions(["verify" => false])
                ->withBody($body, 'text/xml')
                ->post($API_CANCEL_KEY);

            if ($response->status() === 200 && !$response->failed()) {
                $p = xml_parser_create();
                xml_parse_into_struct($p, $response->body(), $vals, $index);
                xml_parser_free($p);

                foreach ($vals as $items) {
                    if (isset($items['value']) && $items['tag'] === "CODE") {
                        if ($items['value'] === '100') {
                            $cancelacionRegistro = new PROC_CANCELED_CFDI();
                            $cancelacionRegistro->canceledCfdi_module = $venta->cfdi_module;
                            $cancelacionRegistro->canceledCfdi_moduleID = $venta->cfdi_moduleID;
                            $cancelacionRegistro->canceledCfdi_movementID = $venta->cfdi_movementID;
                            $cancelacionRegistro->canceledCfdi_total = $venta->cfdi_total;
                            $cancelacionRegistro->canceledCfdi_receptor = $venta->cfdi_RFC;
                            $cancelacionRegistro->canceledCfdi_company = $venta->cfdi_company;
                            $cancelacionRegistro->canceledCfdi_branchOffice = $venta->cfdi_branchOffice;
                            $cancelacionRegistro->canceledCfdi_status = "Vigente";
                            $cancelacionRegistro->canceledCfdi_Uuid = $venta->cfdi_UUID;
                            $cancelacionRegistro->save();
                        }
                    }
                }
            } else {
                return $response->failed();
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }



    //Realizamos la cancelacion del CFDI
    public function cancelarCxC($id_CxC)
    {
        try {
            $empresa = session('company');
            $API_KEY = env('PAC_ADVANCE_KEY');
            $API_CANCEL_KEY = env('PAC_ADVANCE_WEB_CANCEL');
            //Creamos el archivo donde guardaremos la llave privada
            $rutaCer = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeCertificate));
            $rutaKey = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeKey));
            $rutaBase = $empresa->companies_routeFiles . "CFDI/llaveprivada.key.pem";
            $urlArchivoTemporal = str_replace('/', '\\', Storage::path("empresas/" . $rutaBase));
            $keyDerPass = trim(Crypt::decrypt($empresa->companies_passwordKey));
            // $openssl = new \CfdiUtils\OpenSSL\OpenSSL();



            //Validamos si existe el archivo llaveprivada.key.pem
            if (Storage::disk('empresas')->exists($rutaBase)) {
                //Si existe lo eliminamos
                Storage::disk('empresas')->delete($rutaBase);
            }

            //Otra manera de desencrptar el archivo key de la empresa
            exec('openssl pkcs8 -inform DER -in ' . $rutaKey . ' -passin pass:' . $keyDerPass . ' -out ' . $urlArchivoTemporal, $result, $status);

            if ($status === 1) {
                $this->setMensaje('Error al timbrar: Error al obtener la llave privada desencriptada');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }


            //Buscamos el archivo llaveprivada.key.pem
            $llavePrivadaPem = Storage::disk('empresas')->get($rutaBase);
            $cxc = PROC_CFDI::WHERE('cfdi_module', '=', 'CxC')->WHERE('cfdi_moduleID', '=', $id_CxC)->first();
            $motivoCancelaciónCxc = PROC_CANCELED_REASON::WHERE('canceledReason_moduleID', '=', $id_CxC)->WHERE('canceledReason_module', '=', 'CxC')->first();

            $certificado = new \CfdiUtils\Certificado\Certificado($rutaCer);


            $cuerpoInicio = '<?xml version="1.0" encoding="utf-8"?>
            <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
              <soap:Body>
              <CancelarRequest>
                  <CancelarRequest>
                    <PrivateKeyPem>' . $llavePrivadaPem . '</PrivateKeyPem>
                    <PublicKeyPem>' . $certificado->getPemContents() . '</PublicKeyPem>
                    <Uuid>' . $cxc->cfdi_UUID . '</Uuid>
                    <RfcReceptor>' . $cxc->cfdi_RFC . '</RfcReceptor>
                    <Total>' . $cxc->cfdi_total . '</Total>
                    <Motivo>' . $motivoCancelaciónCxc->canceledReason_reason . '</Motivo>';
            $cuerpoFinal = '</CancelarRequest>
                </CancelarRequest>
              </soap:Body>
            </soap:Envelope>
            ';

            if ($motivoCancelaciónCxc->canceledReason_reason == "01") {
                $body = $cuerpoInicio . '<FolioSustitucion>' . $motivoCancelaciónCxc->canceledReason_sustitutionUuid . '</FolioSustitucion>' . $cuerpoFinal;
            } else {
                $body = $cuerpoInicio . $cuerpoFinal;
            }

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Authorization' => $API_KEY, 'Cache-Control' => 'no-cache', 'Content-Type' => 'text/xml',
                    'SOAPAction' => 'urn:advans-cfdi-cancelacion#Cancelar',
                ])
                ->withOptions(["verify" => false])
                ->withBody($body, 'text/xml')
                ->post($API_CANCEL_KEY);

            if ($response->status() === 200 && !$response->failed()) {
                $p = xml_parser_create();
                xml_parse_into_struct($p, $response->body(), $vals, $index);
                xml_parser_free($p);

                foreach ($vals as $items) {
                    if (isset($items['value']) && $items['tag'] === "CODE") {
                        if ($items['value'] === '100') {
                            $cancelacionRegistro = new PROC_CANCELED_CFDI();
                            $cancelacionRegistro->canceledCfdi_module = $cxc->cfdi_module;
                            $cancelacionRegistro->canceledCfdi_moduleID = $cxc->cfdi_moduleID;
                            $cancelacionRegistro->canceledCfdi_movementID = $cxc->cfdi_movementID;
                            $cancelacionRegistro->canceledCfdi_total = $cxc->cfdi_total;
                            $cancelacionRegistro->canceledCfdi_receptor = $cxc->cfdi_RFC;
                            $cancelacionRegistro->canceledCfdi_company = $cxc->cfdi_company;
                            $cancelacionRegistro->canceledCfdi_branchOffice = $cxc->cfdi_branchOffice;
                            $cancelacionRegistro->canceledCfdi_status = "Vigente";
                            $cancelacionRegistro->canceledCfdi_Uuid = $cxc->cfdi_UUID;
                            $cancelacionRegistro->save();
                        }
                    }
                }
            } else {
                return $response->failed();
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }



    public function timbrarCXC($idCxc, $request)
    {
        //Obtenemos la informacion de la empresa
        $empresa = session('company');


        if ($empresa->companies_routeCertificate == null || $empresa->companies_routeKey == null) {
            $this->setMensaje('Error al timbrar: Error al obtener los archivos de SAT de la empresa');
            $this->setStatus(false);
            $this->setStatus2(true);
            return;
            // return response()->json(['error' => 'No se ha configurado la ruta de los archivos de la empresa'], 500);
        }
        $rutaCer = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeCertificate));
        $rutaKey = str_replace('/', '\\', Storage::path("empresas/" . $empresa->companies_routeKey));
        $informacionMovimiento = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $idCxc)->first();
        // dd($informacionMovimiento);
        $cliente = CAT_CUSTOMERS::where('customers_key', '=', $informacionMovimiento->accountsReceivable_customer)->first();
        $moneda = CONF_MONEY::where('money_key', '=', $informacionMovimiento->accountsReceivable_money)->first();
        $formaPago = CONF_FORMS_OF_PAYMENT::where('formsPayment_key', '=', $informacionMovimiento->accountsReceivable_formPayment)->first();
        $concepto = CONF_MODULES_CONCEPT::where('moduleConcept_name', '=', $informacionMovimiento->accountsReceivable_concept)->where('moduleConcept_module', '=', 'Cuentas por Cobrar')->first();


        //validamos que los movimientos esten timbrados para realizar la relacion con otros movimientos
        $facturas = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', $informacionMovimiento->accountsReceivable_id)->where('accountsReceivableDetails_branchOffice', '=', $informacionMovimiento->accountsReceivable_branchOffice)->WHERE('accountsReceivableDetails_company', '=', $informacionMovimiento->accountsReceivable_company)->get();
        //validamos que anticipo este timbrado para realizar la relacion con otros movimientos


        $isTimbradoMovimientos = false;
        if ($informacionMovimiento->accountsReceivable_movement == 'Aplicación' || $informacionMovimiento->accountsReceivable_movement == 'Cobro de Facturas') {
            foreach ($facturas as $factura) {
                //revisamos la factura en ventas
                $cxFactura = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $factura->accountsReceivableDetails_movReference)->where('accountsReceivable_branchOffice', '=', $factura->accountsReceivableDetails_branchOffice)->WHERE('accountsReceivable_company', '=', $factura->accountsReceivableDetails_company)->first();

                if ($cxFactura->accountsReceivable_stamped == "1") {
                    $isTimbradoMovimientos = true;
                } else {
                    $isTimbradoMovimientos = false;
                }
            }

            if (!$isTimbradoMovimientos) {
                $this->setMensaje('Error al timbrar: Las facturas seleccionadas no estan timbradas');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }
        } elseif ($informacionMovimiento->accountsReceivable_movement == 'Devolución de Anticipo') {
            $cxcAnticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', $informacionMovimiento->accountsReceivable_origin)
                ->where('accountsReceivable_movementID', '=', $informacionMovimiento->accountsReceivable_originID)
                ->where('accountsReceivable_branchOffice', '=', $informacionMovimiento->accountsReceivable_branchOffice)->WHERE('accountsReceivable_company', '=', $informacionMovimiento->accountsReceivable_company)->first();

            if ($cxcAnticipo->accountsReceivable_stamped == "1") {
                $isTimbradoMovimientos = true;
            } else {
                $isTimbradoMovimientos = false;
            }

            if (!$isTimbradoMovimientos) {
                $this->setMensaje('Error al timbrar: El anticipo seleccionado no esta timbrado');
                $this->setStatus(false);
                $this->setStatus2(true);
                return;
            }
        }


        //generar el xml para facturacion
        $certificado = new \CfdiUtils\Certificado\Certificado($rutaCer);

        //Creamos el archivo donde guardaremos la llave privada
        $rutaBase = $empresa->companies_routeFiles . "CFDI/llaveprivada.key.pem";
        $urlArchivoTemporal = str_replace('/', '\\', Storage::path("empresas/" . $rutaBase));
        $keyDerPass = trim(Crypt::decrypt($empresa->companies_passwordKey));
        // $openssl = new \CfdiUtils\OpenSSL\OpenSSL();

        //Validamos si existe el archivo llaveprivada.key.pem
        if (Storage::disk('empresas')->exists($rutaBase)) {
            //Si existe lo eliminamos
            Storage::disk('empresas')->delete($rutaBase);
        }

        //Otra manera de desencrptar el archivo key de la empresa
        exec('openssl pkcs8 -inform DER -in ' . $rutaKey . ' -passin pass:' . $keyDerPass . ' -out ' . $urlArchivoTemporal, $result, $status);

        if ($status === 1) {
            $this->setMensaje('Error al timbrar: Error al obtener la llave privada desencriptada');
            $this->setStatus(false);
            $this->setStatus2(true);
            return;
        }

        // $openssl->derKeyConvert($rutaKey, $keyDerPass, $urlArchivoTemporal);

        //Buscamos el archivo llaveprivada.key.pem
        $llavePrivadaPem = Storage::disk('empresas')->get($rutaBase);

        $tipoComprobante = [
            'Anticipo' => 'I',
            'Aplicación' => 'E',
            'Cobro' => 'P',
            'Devolución de Anticipo' => 'E',
        ];


        $formaPagoArray = [
            'FormaPago' => $formaPago->formsPayment_sat,
            'MetodoPago' => 'PUE',
            'TipoCambio' => intval($moneda->money_change),
        ];
        $comprobanteAtributos = [
            'LugarExpedicion' => $empresa->companies_cp,
            // 'Confirmacion' => 'ECVH1',
            'TipoDeComprobante' =>  $tipoComprobante[($informacionMovimiento->accountsReceivable_movement === "Cobro de Facturas") ? 'Cobro' : (($informacionMovimiento->accountsReceivable_movement === "Anticipo Clientes") ? 'Anticipo' : $informacionMovimiento->accountsReceivable_movement)],

            'Moneda' => $informacionMovimiento->accountsReceivable_movement === "Cobro de Facturas" ? 'XXX' : $moneda->money_keySat,
            'Fecha' => Carbon::now()->format('Y-m-d\TH:i:s'),
            'Exportacion' => '01',
            'Folio' => $informacionMovimiento->accountsReceivable_movementID,
            'SubTotal' => 0,
            'Total' => 0,
        ];

        if ($informacionMovimiento->accountsReceivable_movement !== "Cobro de Facturas") {
            $comprobanteAtributos = array_merge($comprobanteAtributos, $formaPagoArray);
        }



        $creator = new \CfdiUtils\CfdiCreator40($comprobanteAtributos, $certificado);

        $comprobante = $creator->comprobante();

        // dd($venta,$articulosVenta, $cliente, $condicionCredito, $metodoPago, $empresa, $moneda, $comprobanteAtributos, $certificado);

        $comprobante->addEmisor([
            'Rfc' => trim($empresa->companies_rfc),
            'Nombre' => trim($empresa->companies_name),
            'RegimenFiscal' => $empresa->companies_taxRegime,
        ]);


        $comprobante->addReceptor([
            'Rfc' => trim($cliente->customers_RFC),
            'Nombre' => trim($cliente->customers_businessName),
            'UsoCFDI' => $cliente->customers_identificationCFDI,
            'RegimenFiscalReceptor' => $cliente->customers_taxRegime,
            'DomicilioFiscalReceptor' => $cliente->customers_cp,
        ]);


        // //Validamos en caso de que sea una factura con relación
        $dataFacturaInfo = json_decode($request->dataFacturaInfo, true);

        if (!$dataFacturaInfo['Normal']) {
            $facturaRelacion = PROC_CFDI::WHERE('cfdi_module', '=', 'CxC')->WHERE('cfdi_moduleID', '=', $dataFacturaInfo['facturaRelacion'])->first();
            $comprobante->addCfdiRelacionados(['TipoRelacion' => '04'])->addCfdiRelacionado([
                'UUID' => $facturaRelacion->cfdi_UUID
            ]);
        }

        if ($informacionMovimiento->accountsReceivable_movement == 'Anticipo Clientes') {
            $comprobante->addConcepto([
                'ClaveProdServ' => explode('-', $concepto->moduleConcept_prodServ)[0],
                'Cantidad' => 1,
                'ClaveUnidad' => 'ACT',
                'Unidad' => 'ACT',
                'Descripcion' => $concepto->moduleConcept_name,
                'ValorUnitario' => number_format($informacionMovimiento->accountsReceivable_amount, 2, '.', ''),
                'Importe' => number_format($informacionMovimiento->accountsReceivable_amount, 2, '.', ''),
                'ObjetoImp' => '02',
            ])->addTraslado([
                'Base' => number_format($informacionMovimiento->accountsReceivable_amount, 2, '.', ''),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => '0.160000',
                'Importe' => number_format($informacionMovimiento->accountsReceivable_amount  * 0.16, 2, '.', ''),
            ]);
        }


        $facturas = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', $informacionMovimiento->accountsReceivable_id)->where('accountsReceivableDetails_branchOffice', '=', $informacionMovimiento->accountsReceivable_branchOffice)->WHERE('accountsReceivableDetails_company', '=', $informacionMovimiento->accountsReceivable_company)->get();

        if ($informacionMovimiento->accountsReceivable_movement == 'Aplicación') {

            $relacionFacturas =  $comprobante->addCfdiRelacionados([
                'TipoRelacion' => '07',
            ]);


            $totalApagar = 0;
            foreach ($facturas as $factura) {
                $cxFactura = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $factura->accountsReceivableDetails_movReference)->where('accountsReceivable_branchOffice', '=', $factura->accountsReceivableDetails_branchOffice)->WHERE('accountsReceivable_company', '=', $factura->accountsReceivableDetails_company)->first();

                //revisamos la factura en ventas
                $venta = PROC_SALES::where('sales_movement', $cxFactura->accountsReceivable_origin)->where('sales_movementID', '=', $cxFactura->accountsReceivable_originID)->where('sales_branchOffice', '=', $cxFactura->accountsReceivable_branchOffice)->WHERE('sales_company', '=', $cxFactura->accountsReceivable_company)->where('sales_status', '=', 'FINALIZADO')->first();

                //revismos el uuid de la factura

                $cfdiVentaFactura = PROC_CFDI::where('cfdi_moduleID', '=', $venta->sales_id)->where('cfdi_movementID', '=', $venta->sales_movementID)->WHERE('cfdi_company', '=', $venta->sales_company)->where('cfdi_branchOffice', '=', $venta->sales_branchOffice)->where("cfdi_module", '=', 'Ventas')->select('cfdi_UUID')->first();

                $relacionFacturas->addCfdiRelacionado([
                    'UUID' => $cfdiVentaFactura->cfdi_UUID,
                ]);

                $totalApagar += $factura->accountsReceivableDetails_amount;
            }

            //Al total de la aplicacion le restamos el iva
            $iva = number_format($totalApagar * 0.16, 2, '.', '');
            $importeBase = number_format($totalApagar - $iva, 2, '.', '');



            $comprobante->addConcepto([
                'ClaveProdServ' => explode('-', $concepto->moduleConcept_prodServ)[0],
                'Cantidad' => 1,
                'ClaveUnidad' => 'ACT',
                'Unidad' => 'ACT',
                'Descripcion' => $concepto->moduleConcept_name,
                'ValorUnitario' => $importeBase,
                'Importe' => $importeBase,
                'ObjetoImp' => '02',
            ])->addTraslado([
                'Base' => number_format($totalApagar, 2, '.', ''),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => '0.160000',
                'Importe' => number_format($totalApagar * 0.16, 2, '.', ''),
            ]);
        }

        $retencionesIVA = 0;
        $retencionesISR = 0;
        if ($informacionMovimiento->accountsReceivable_movement == 'Cobro de Facturas') {
            $comprobante->addConcepto([
                'ClaveProdServ' => explode('-', $concepto->moduleConcept_prodServ)[0],
                'Cantidad' => 1,
                'ClaveUnidad' => 'ACT',
                'Descripcion' => $concepto->moduleConcept_name,
                'ValorUnitario' => 0,
                'Importe' => 0,
                'ObjetoImp' => '01',
            ]);

            //Complemento de tipo pago
            $creadorPago = new \CfdiUtils\Elements\Pagos20\Pagos();
            $pagosTotales = 0;
            $TotalTrasladosBaseIVA16 = 0;
            $TotalTrasladosImpuestoIVA16 = 0;
            $TotalRetencionesISR = 0;
            $TotalRetencionesIVA = 0;

            foreach ($facturas as $factura) {


                $cxFactura = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $factura->accountsReceivableDetails_movReference)->where('accountsReceivable_branchOffice', '=', $factura->accountsReceivableDetails_branchOffice)->WHERE('accountsReceivable_company', '=', $factura->accountsReceivableDetails_company)->first();

                //revisamos la factura en ventas
                $venta = PROC_SALES::where('sales_movement', $cxFactura->accountsReceivable_origin)->where('sales_movementID', '=', $cxFactura->accountsReceivable_originID)->where('sales_branchOffice', '=', $cxFactura->accountsReceivable_branchOffice)->WHERE('sales_company', '=', $cxFactura->accountsReceivable_company)->where('sales_status', '=', 'FINALIZADO')->first();

                $cfdiVentaFactura = PROC_CFDI::where('cfdi_moduleID', '=', $venta->sales_id)->where('cfdi_movementID', '=', $venta->sales_movementID)->WHERE('cfdi_company', '=', $venta->sales_company)->where('cfdi_branchOffice', '=', $venta->sales_branchOffice)->where("cfdi_module", '=', 'Ventas')->select('cfdi_UUID')->where('cfdi_cancelled', '=', 0)->first();

                //revisamos si la factura ya fue pagada por primera vez
                $cobrosPagados = PROC_CFDI_CXC_REFERENCE::JOIN('PROC_CFDI', 'PROC_CFDI_CXC_REFERENCE.cfdiReferenceCxC_cxcID', '=', 'PROC_CFDI.cfdi_moduleID')->WHERE("PROC_CFDI_CXC_REFERENCE.cfdiReferenceCxC_idOrigin", '=', $venta->sales_id)->WHERE('PROC_CFDI_CXC_REFERENCE.cfdiReferenceCxC_UUID', '=', $cfdiVentaFactura->cfdi_UUID)->WHERE('PROC_CFDI_CXC_REFERENCE.cfdiReferenceCxC_move', '=', 'Cobro de Facturas')->WHERE('PROC_CFDI.cfdi_cancelled', '=', 0)->WHERE('PROC_CFDI.cfdi_module', '=', 'CxC')->selectRaw('count(*) as pagosFactura')->get();

                if ($cobrosPagados !== Null) {
                    $parcialidad = $cobrosPagados[0]->pagosFactura + 1;
                } else {
                    $parcialidad = 1;
                }

                if ($cxFactura->accountsReceivable_balance === "0.00" || $cxFactura->accountsReceivable_balance === ".0000") {
                    $saldoAnterior = number_format($factura->accountsReceivableDetails_amount, 2, '.', '');
                    $saldoInsoluto = 0.00;
                } else {
                    $saldoAnterior = number_format($cxFactura->accountsReceivable_balance + $factura->accountsReceivableDetails_amount, 2, '.', '');
                    $saldoInsoluto = number_format($saldoAnterior - $factura->accountsReceivableDetails_amount, 2, '.', '');
                }

                $montoApagar = $factura->accountsReceivableDetails_amount;
                $montoConRetencionFactura = $cxFactura->accountsReceivable_total;
                $montoSinRetencionFactura = $cxFactura->accountsReceivable_amount;

                $portActualAbono = (($montoApagar * 100) / $montoConRetencionFactura) / 100;


                $montoAbono = number_format($montoSinRetencionFactura * $portActualAbono, 2, '.', '');

                $tipoCambio = number_format($moneda->money_change, 4, '.', '');
                // dd($tipoCambio);
                $pagosTotales += ($factura->accountsReceivableDetails_amount * $tipoCambio);
                $importeBase = number_format($montoAbono, 2, '.', '');
                $TotalTrasladosBaseIVA16 += number_format($importeBase * $tipoCambio, 2, '.', '');
                $iva = number_format($importeBase * 0.16, 2, '.', '');
                $TotalTrasladosImpuestoIVA16 += number_format($iva * $tipoCambio, 2, '.', '');

                $pago = $creadorPago->addPago([
                    'FechaPago' => Carbon::now()->format('Y-m-d\TH:i:s'),
                    'FormaDePagoP' => $formaPago->formsPayment_sat,
                    'TipoCambioP' =>  $tipoCambio,
                    'MonedaP' => $moneda->money_keySat,
                    'Monto' => number_format($factura->accountsReceivableDetails_amount, 2, '.', ''),
                ]);

                $cuerpoPagoDR = $pago->addDoctoRelacionado([
                    'IdDocumento' => $cfdiVentaFactura->cfdi_UUID,
                    'MonedaDR' => $moneda->money_keySat,
                    'NumParcialidad' => $parcialidad,
                    'ImpSaldoAnt' => number_format($saldoAnterior, 2, '.', ''),
                    'ImpPagado' => number_format($factura->accountsReceivableDetails_amount, 2, '.', ''),
                    'ImpSaldoInsoluto' => $saldoInsoluto,
                    'EquivalenciaDR' => "1",
                    'ObjetoImpDR' => '02',
                    'Folio' => $venta->sales_movementID,
                ])->addImpuestosDR();

                if ($factura->accountsReceivableDetails_retention2 != null && $factura->accountsReceivableDetails_retention2 != 0) {
                    $ivaR = number_format(floatval($factura->accountsReceivableDetails_retention2) / 100, 6, '.', '');

                    $cuerpoPagoDR->addRetencionesDR()->addRetencionDR([
                        'TipoFactorDR' => 'Tasa',
                        'TasaOCuotaDR' => $ivaR,
                        'ImpuestoDR' => '002',
                        'BaseDR' => $montoAbono,
                        'ImporteDR' =>  number_format($montoAbono * $ivaR, 2, '.', ''),
                    ]);
                }

                if ($factura->accountsReceivableDetails_retention1 != null && $factura->accountsReceivableDetails_retention1 != 0) {
                    $isr = number_format(floatval($factura->accountsReceivableDetails_retention1) / 100, 6, '.', '');
                    $cuerpoPagoDR->addRetencionesDR()->addRetencionDR([
                        'TipoFactorDR' => 'Tasa',
                        'TasaOCuotaDR' => $isr,
                        'ImpuestoDR' => '001',
                        'BaseDR' => $montoAbono,
                        'ImporteDR' =>  number_format($montoAbono * $isr, 2, '.', ''),
                    ]);
                }



                $cuerpoPagoDR->addTrasladosDR()->addTrasladoDR([
                    'ImpuestoDR' => '002',
                    'TipoFactorDR' => 'Tasa',
                    'TasaOCuotaDR' => '0.160000',
                    'ImporteDR' => $iva,
                    'BaseDR' => $importeBase,
                ]);

                $cuerpoPagoP =  $pago->addImpuestosP();



                if ($factura->accountsReceivableDetails_retention2 != null && $factura->accountsReceivableDetails_retention2 != 0) {
                    $ivaR = number_format(floatval($factura->accountsReceivableDetails_retention2) / 100, 6, '.', '');
                    $retencionesIVA = floatval(number_format($montoAbono * $ivaR, 2, '.', ''));
                    $TotalRetencionesIVA += $retencionesIVA;
                    $facturaUpdateCxcDetails = PROC_ACCOUNTS_RECEIVABLE_DETAILS::WHERE('accountsReceivableDetails_id', '=', $factura->accountsReceivableDetails_id)->first();
                    $facturaUpdateCxcDetails->accountsReceivableDetails_retentionIVA = $retencionesIVA;
                    $facturaUpdateCxcDetails->save();
                    $cuerpoPagoP->addRetencionesP()->addRetencionP([
                        'ImpuestoP' => '002',
                        'ImporteP' =>  number_format($montoAbono * $ivaR, 2, '.', ''),
                    ]);
                }

                if ($factura->accountsReceivableDetails_retention1 != null && $factura->accountsReceivableDetails_retention1 != 0) {
                    $isr = number_format(floatval($factura->accountsReceivableDetails_retention1) / 100, 6, '.', '');
                    $retencionesISR = floatval(number_format($montoAbono * $isr, 2, '.', ''));
                    $TotalRetencionesISR += $retencionesISR;

                    $facturaUpdateCxcDetails = PROC_ACCOUNTS_RECEIVABLE_DETAILS::WHERE('accountsReceivableDetails_id', '=', $factura->accountsReceivableDetails_id)->first();

                    $facturaUpdateCxcDetails->accountsReceivableDetails_retentionISR = $retencionesISR;
                    $facturaUpdateCxcDetails->save();

                    $cuerpoPagoP->addRetencionesP()->addRetencionP([
                        'ImpuestoP' => '001',
                        'ImporteP' =>  number_format($montoAbono * $isr, 2, '.', ''),
                    ]);
                }

                $cuerpoPagoP->addTrasladosP()->addTrasladoP(
                    [
                        'ImpuestoP' => '002',
                        'TipoFactorP' => 'Tasa',
                        'TasaOCuotaP' => '0.160000',
                        'ImporteP' => $iva,
                        'BaseP' => $importeBase,
                    ]
                );


                //Actualizamos los totales
                $cxcRetencionesUpdate = PROC_ACCOUNTS_RECEIVABLE::WHERE('accountsReceivable_id', '=', $idCxc)->first();
                $cxcRetencionesUpdate->accountsReceivable_retentionISR = $TotalRetencionesISR;
                $cxcRetencionesUpdate->accountsReceivable_retentionIVA = $TotalRetencionesIVA;
                $cxcRetencionesUpdate->save();
            }

            //  dd($TotalTrasladosBaseIVA16, $TotalTrasladosImpuestoIVA16, $retencionesIVA ,$retencionesISR);
            $creadorPago->addTotales([
                'TotalTrasladosBaseIVA16' => $TotalTrasladosBaseIVA16,
                'TotalTrasladosImpuestoIVA16' => $TotalTrasladosImpuestoIVA16,
                'MontoTotalPagos' => number_format($pagosTotales, 2, '.', ''),
                'TotalRetencionesISR' => number_format($TotalRetencionesISR * $tipoCambio, 2, '.', ''),
                'TotalRetencionesIVA' => number_format($TotalRetencionesIVA * $tipoCambio, 2, '.', ''),

            ]);



            $comprobante->addComplemento($creadorPago);
        }

        if ($informacionMovimiento->accountsReceivable_movement == 'Devolución de Anticipo') {

            $relacionFacturas =  $comprobante->addCfdiRelacionados([
                'TipoRelacion' => '01',
            ]);

            $totalPagar = number_format($informacionMovimiento->accountsReceivable_amount / 1.16, 2, '.', '');
            $comprobante->addConcepto([
                'ClaveProdServ' => explode('-', $concepto->moduleConcept_prodServ)[0],
                'Cantidad' => 1,
                'ClaveUnidad' => 'ACT',
                'Unidad' => 'ACT',
                'Descripcion' => $concepto->moduleConcept_name,
                'ValorUnitario' => number_format($totalPagar, 2, '.', ''),
                'Importe' => number_format($totalPagar, 2, '.', ''),
                'ObjetoImp' => '02',
            ])->addTraslado([
                'Base' => number_format($totalPagar, 2, '.', ''),
                'Impuesto' => '002',
                'TipoFactor' => 'Tasa',
                'TasaOCuota' => '0.160000',
                'Importe' => number_format($totalPagar * 0.16, 2, '.', ''),
            ]);

            //Buscamos el anticipo
            $anticipo = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_movement', $informacionMovimiento->accountsReceivable_origin)
                ->where('accountsReceivable_movementID', '=', $informacionMovimiento->accountsReceivable_originID)
                ->where('accountsReceivable_branchOffice', '=', $informacionMovimiento->accountsReceivable_branchOffice)->WHERE('accountsReceivable_company', '=', $informacionMovimiento->accountsReceivable_company)->first();
            // dd($anticipo);
            $cfdiAnticipo = PROC_CFDI::WHERE('cfdi_module', '=', 'CxC')->WHERE('cfdi_moduleID', '=', $anticipo->accountsReceivable_id)->WHERE('cfdi_company', '=', $informacionMovimiento->accountsReceivable_company)->where('cfdi_branchOffice', '=', $informacionMovimiento->accountsReceivable_branchOffice)->first();
            // dd($cfdiAnticipo);

            $relacionFacturas->addCfdiRelacionado([
                'UUID' => $cfdiAnticipo->cfdi_UUID,
            ]);
        }


        // método de ayuda para establecer las sumas del comprobante e impuestos
        // // con base en la suma de los conceptos y la agrupación de sus impuestos

        if ($informacionMovimiento->accountsReceivable_movement !== 'Cobro de Facturas') {
            $creator->addSumasConceptos(null, 2);
        }

        // // método de ayuda para generar el sello (obtener la cadena de origen y firmar con la llave privadacorreo
        $creator->addSello($llavePrivadaPem);

        // // método de ayuda para mover las declaraciones de espacios de nombre al nodo raíz
        $creator->moveSatDefinitionsToComprobante();

        // // método de ayuda para validar usando las validaciones estándar de creación de la librería
        $asserts = $creator->validate();
        $asserts->hasErrors(); // true o false

        // método de ayuda para generar el xml y guardar los contenidos en un archivo
        // $creator->saveXml('C:\inetpub\wwwroot\meinsur\storage\app\empresas\Ruta\CFDI\cfdi-sin-Timbrar.xml');

        // // método de ayuda para generar el xml y retornarlo como un string
        $xml = $creator->asXml();
        // dd($xml);


        $mailCliente = [$cliente->customers_mail1, $cliente->customers_mail2];
        if ($cliente->customers_mail1 !== null && $cliente->customers_mail2 !== null) {
            $mailCliente = [$cliente->customers_mail1, $cliente->customers_mail2];
        } else if ($cliente->customers_mail1 !== null && $cliente->customers_mail2 === null) {
            $mailCliente = [$cliente->customers_mail1];
        } else if ($cliente->customers_mail1 === null && $cliente->customers_mail2 !== null) {
            $mailCliente = [$cliente->customers_mail2];
        } else {
            $mailCliente = [];
        }

        //Validamos si existe el archivo llaveprivada.key.pem
        if (Storage::disk('empresas')->exists($rutaBase)) {
            //Si existe lo eliminamos
            Storage::disk('empresas')->delete($rutaBase);
        }

        $this->facturacionCXC($xml, $cliente, $informacionMovimiento, $mailCliente, $informacionMovimiento->accountsReceivable_movement, $request);
    }

    //Enviamos el xml facturado al servicio PROC ADVANCE
    public function facturacionCXC($xml, $cliente, $cxc, $mailCliente, $movimiento, $request)
    {
        $cfdi = $xml;
        $API_KEY = env('PAC_ADVANCE_KEY');
        $API_WEB = env('PAC_ADVANCE_WEB');


        try {
            //Configuramos el cliente soap
            $client = new \SoapClient($API_WEB);

            $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

            if ($parametro === null || $parametro->generalParameters_filesCustomers === Null || $parametro->generalParameters_filesCustomers === '') {
                $empresaRuta = session('company')->companies_routeFiles . 'Clientes';
            } else {
                $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesCustomers;
            }

            //si ocurre un error en el cliente soap
            $err = $client->__getLastResponse();
            if ($err) {
                dd($err);
            }

            //ejecutamos la llamada al metodo
            $result = $client->__soapCall('timbrar2', array('credential' => $API_KEY, 'cfdi' => $cfdi,));

            //actualizamos el xml con el timbre


            if ($result->Code === "307" || $result->Code === "200") {
                //formamos la ruta del xml timbrado
                $año = Carbon::now()->year;
                $mes = Carbon::now()->month;
                $urlXmlFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $cliente->customers_key . '/CFDI/' . $año . '/' . $mes . '/' . $cxc->accountsReceivable_movement . '-folio-' . $cxc->accountsReceivable_movementID . '.xml');
                //guardamos el xml con el timbre
                $xmlTimbrado = new SimpleXMLElement($result->CFDI);
                $timbraFactura2 = PROC_ACCOUNTS_RECEIVABLE::WHERE("accountsReceivable_id", "=", $cxc->accountsReceivable_id)->first();
                $timbraFactura2->accountsReceivable_stamped = 1;
                $timbraFactura2->update();
                //Validamos en caso de que sea una factura con relación
                $dataFacturaInfo = json_decode($request->dataFacturaInfo, true);
                $canceledReference = new PROC_CANCELED_REFERENCE();
                if (!$dataFacturaInfo['Normal']) {
                    $canceledReference->canceledReference_module = 'CxC';
                    $canceledReference->canceledReference_moduleID = $cxc->accountsReceivable_id;
                    $canceledReference->canceledReference_moduleCanceledID = $dataFacturaInfo['facturaRelacion'];
                    $canceledReference->save();
                } else {
                    $canceledReference->canceledReference_module = 'CxC';
                    $canceledReference->canceledReference_moduleID = $cxc->accountsReceivable_id;
                    $canceledReference->save();
                }

                Storage::disk('empresas')->put($urlXmlFinal, $xmlTimbrado->asXML());
                // $xmlTimbrado->saveXml('C:\inetpub\wwwroot\meinsur\storage\app\empresas\Ruta\CFDI\cfdi2.xml');
                $this->generarPDFCXC($cliente, $urlXmlFinal, $cxc, $mailCliente, $movimiento);
                $this->setMensaje('Movimiento Timbrado Correctamente');
                $this->setStatus(true);
                $this->setStatus2(true);
            } else {
                $this->setMensaje('Error al timbrar: ' . $result->Message);
                $this->setStatus(false);
                $this->setStatus2(true);
            }
        } catch (\SoapFault $fault) {
            trigger_error("SOAP Fault: (faultcode: " . $fault->faultcode . ", faultstring: " . $fault->faultstring . ")", E_USER_ERROR);
            $this->setStatus2(false);
        } catch (\Exception $e) {
            trigger_error("SOAP Error: " . $e->getMessage() . ' linea: ' . $e->getLine(), E_USER_ERROR);
            $this->setStatus2(false);
        }
    }

    public function generarPDFCXC($cliente, $rutaCFDI, $cxc, $mailCliente, $movimiento)
    {
        $parametro = CONF_GENERAL_PARAMETERS::where('generalParameters_company', '=', session('company')->companies_key)->first();

        if ($parametro === null || $parametro->generalParameters_filesCustomers === Null || $parametro->generalParameters_filesCustomers === '') {
            $empresaRuta = session('company')->companies_routeFiles . 'Clientes';
        } else {
            $empresaRuta = session('company')->companies_routeFiles . $parametro->generalParameters_filesCustomers;
        }

        $CFDI_XML = Storage::disk('empresas')->get($rutaCFDI);

        $id = $cliente->customers_key; //poner parametro a la funcion
        $XML = \CfdiUtils\Cfdi::newFromString($CFDI_XML)->getQuickReader(); //poner ruta del xml timbrado en el parametro de la funcion
        $impuestos = $XML->Impuestos;

        //Obtenemos la direccion de la empresa
        $empresa = session('company');
        $direccion = $empresa->companies_addres . ', CP:' . $empresa->companies_cp . ',' . $empresa->companies_country . ',' . $empresa->companies_state . '-' . $empresa->companies_suburb;

        //Obtenemos la direccion del cliente

        $colonia = explode("-", $cliente->customers_colonyFractionation)[0];
        // dd($colonia);
        $direccionCliente = $cliente->customers_addres . ' ' . $cliente->customers_roads . ' Exterior: ' . $cliente->customers_outdoorNumber . ' Interior: ' . $cliente->customers_interiorNumber . ' Col. ' . $colonia . ', CP: ' . $cliente->customers_cp . ', ' . $cliente->customers_country . ', ' . $cliente->customers_state;

        if (isset($impuestos['TotalImpuestosTrasladados'])) {
            $impuestos = $impuestos['TotalImpuestosTrasladados'];
        } else {
            $impuestos = 0;
        }

        //sacar al emisor
        $emisor = $XML->Emisor;

        $regimenEmisor = CAT_SAT_REGIMENFISCAL::where('c_RegimenFiscal', $emisor['RegimenFiscal'])->first();

        //sacar al receptor
        $receptor = $XML->Receptor;

        $regimenReceptor = CAT_SAT_REGIMENFISCAL::where('c_RegimenFiscal', $receptor['RegimenFiscalReceptor'])->first();

        // dd($XML);
        $CFDIRelacionado = $XML->CfdiRelacionados;
        // dd($CFDIRelacionado->CfdiRelacionado);

        //sacamos los conceptos
        $conceptos = $XML->Conceptos;


        foreach ($conceptos() as $concepto) {
            $conceptosArray[] = $concepto;
        }

        $conceptosImpuestos = $XML->Conceptos;

        //tenemos que hacer una condicional ya que si $existePartes es true tendremos que hacer un foreach de más, sino, lo dejamos como está
        foreach ($conceptosImpuestos() as $concepto) {
            foreach ($concepto() as $key => $hijos) {
                foreach ($hijos() as $key => $hijo) {
                    foreach ($hijo() as $key => $hijo2) {
                        $conceptosImpuestosArray[] = $hijo2;
                    }
                }
            }
        }

        // dd($conceptosArray);

        //metodo de pago
        $metodoPago = CAT_SAT_METODOPAGO::where('c_MetodoPago', $XML['MetodoPago'])->first();


        $usoCFDI = CAT_SAT_USOCFDI::where('c_UsoCFDI', $receptor['UsoCFDI'])->first();

        //complemento
        $complemento = $XML->Complemento;
        $complementoArray = $complemento->TimbreFiscalDigital;

        //forma de pago

        if ($movimiento !== "Cobro de Facturas") {
            $formaPago = CAT_SAT_FORMAPAGO::where('c_FormaPago', $XML['FormaPago'])->first();
        } else {
            $formaPago = CAT_SAT_FORMAPAGO::where('c_FormaPago', $XML->Complemento->Pagos->Pago['FormaDePagoP'])->first();
        }



        //  dd($conceptosArray, $emisor, $receptor, $XML);

        if (session('company')->companies_logo === null || session('company')->companies_logo === '') {
            $logoFile = null;
        } else {
            $logoFile = Storage::disk('empresas')->get(session('company')->companies_logo);
        }


        if ($logoFile == null) {
            $logoFile = Storage::disk('empresas')->get('default.png');

            if ($logoFile == null) {
                $logoBase64 = '';
            } else {
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
            }
        } else {
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoFile);
        }

        $cfdi = \CfdiUtils\Cfdi::newFromString($CFDI_XML);
        //GENERAR QR
        $parameters = \CfdiUtils\ConsultaCfdiSat\RequestParameters::createFromCfdi($cfdi);
        $qr = $parameters->expression();

        $qrCode = QrCode::size(130)->generate($qr);

        $existePartes = false;

        //convertir svg a png
        $qrEncode = "data:image/png;base64," . base64_encode($qrCode);
        //moneda del cfdi
        $moneda = $XML['Moneda'];

        $movimientoFactura = [
            'movimiento' => $cxc->accountsReceivable_movement,
            'folioMovimiento' => $cxc->accountsReceivable_movementID,
        ];

        if ($movimiento !== "Cobro de Facturas") {
            switch ($movimiento) {
                case 'Anticipo Clientes':
                    $nombreComprobante = 'I-INGRESO';
                    break;

                case 'Aplicación':
                    $nombreComprobante = 'E-EGRESO';
                    break;

                case 'Devolución de Anticipo':
                    $nombreComprobante = 'E-EGRESO';
                    break;

                default:
                    # code...
                    break;
            }



            $pdf = PDF::loadView('include.factura.factura', ['XML' => $XML, 'conceptos' => $conceptosArray, 'emisor' => $emisor, 'receptor' => $receptor, 'regimenEmisor' => $regimenEmisor, 'regimenReceptor' => $regimenReceptor, 'metodoPago' => $metodoPago, 'usoCFDI' => $usoCFDI, 'logo' => $logoBase64, 'complemento' => $complementoArray, 'formaPago' => $formaPago, 'qrEncode' => $qrEncode, 'impuestos' => $impuestos, 'folio' => $cxc->accountsReceivable_movementID, 'direccion' => $direccion, 'direccionCliente' => $direccionCliente, 'moneda' => $moneda, 'movimientoFactura' => $movimientoFactura, 'existePartes' => $existePartes, 'empresa' => $empresa, 'nombreComprobante' => $nombreComprobante, 'conceptosImpuestosArray' => $conceptosImpuestosArray, 'CFDIRelacionado' => $CFDIRelacionado]);
        } else {

            $nombreComprobante = 'CFDI de Pago';
            //Sacamos los documentos relacionados
            $hijosPagos = ($XML->Complemento->Pagos)('pago');
            $documentosRelacionados = [];
            $impuestos = [];

            foreach ($hijosPagos as $pago) {
                $informacion = [
                    "idDocumento" => $pago->DoctoRelacionado['IdDocumento'],
                    "monedaD" => $pago->DoctoRelacionado['MonedaDR'],
                    "NumParcialidad" => $pago->DoctoRelacionado['NumParcialidad'],
                    "Folio" => $pago->DoctoRelacionado['Folio'],
                    "ImpSaldoAnt" => $pago->DoctoRelacionado['ImpSaldoAnt'],
                    "ImpPagado" => $pago->DoctoRelacionado['ImpPagado'],
                    "ImpSaldoInsoluto" => $pago->DoctoRelacionado['ImpSaldoInsoluto'],
                    "ObjImp" => $pago->DoctoRelacionado['ObjetoImpDR'],
                    "EquivalenciaDR" => $pago->DoctoRelacionado['EquivalenciaDR'],
                    "Tipo" => "TrasladoDR",
                    "BaseDR" => $pago->DoctoRelacionado->ImpuestosDR->TrasladosDR->TrasladoDR['BaseDR'],
                    "ImpuestoDR" => $pago->DoctoRelacionado->ImpuestosDR->TrasladosDR->TrasladoDR['ImpuestoDR'],
                    "TipoFactorDR" => $pago->DoctoRelacionado->ImpuestosDR->TrasladosDR->TrasladoDR['TipoFactorDR'],
                    "TasaOCuotaDR" => $pago->DoctoRelacionado->ImpuestosDR->TrasladosDR->TrasladoDR['TasaOCuotaDR'],
                    "ImporteDR" => $pago->DoctoRelacionado->ImpuestosDR->TrasladosDR->TrasladoDR['ImporteDR'],
                ];

                $retenciones = $pago->DoctoRelacionado->ImpuestosDR->RetencionesDR;


                foreach ($retenciones() as $key => $retencion) {
                    $informacion = array_merge($informacion, [
                        "TipoRetencion" . $key => 'RetencionDR',
                        "BaseDR" . $key => $retencion['BaseDR'],
                        "ImpuestoDR" . $key =>  $retencion['ImpuestoDR'],
                        "TipoFactorDR" . $key =>  $retencion['TipoFactorDR'],
                        "TasaOCuotaDR" . $key => $retencion['TasaOCuotaDR'],
                        "ImporteDR" . $key => $retencion['ImporteDR'],
                    ]);
                }
                array_push($documentosRelacionados, $informacion);
            }



            $pdf = PDF::loadView('include.factura.facturaCobro', ['XML' => $XML, 'conceptos' => $conceptosArray, 'emisor' => $emisor, 'receptor' => $receptor, 'regimenEmisor' => $regimenEmisor, 'regimenReceptor' => $regimenReceptor, 'usoCFDI' => $usoCFDI, 'logo' => $logoBase64, 'complemento' => $complementoArray, 'formaPago' => $formaPago, 'qrEncode' => $qrEncode, 'direccion' => $direccion, 'direccionCliente' => $direccionCliente, 'folio' => $cxc->accountsReceivable_movementID, "documentosRelacionados" => $documentosRelacionados, 'moneda' => $moneda, 'empresa' => $empresa, 'nombreComprobante' => $nombreComprobante]);
        }

        //guardar el pdf en la ruta del cliente
        $año = Carbon::now()->year;
        $mes = Carbon::now()->month;
        $urlPdfFinal = str_replace(['//', '///', '////'], '/', $empresaRuta . '/' . $id . '/CFDI/' . $año . '/' . $mes . '/' . $cxc->accountsReceivable_movement . '-folio-' . $cxc->accountsReceivable_movementID . '.pdf');
        //guardamos el xml con el timbre
        Storage::disk('empresas')->put($urlPdfFinal, $pdf->output());

        //Antes de enviar el correo, guardamos las rutas del CFDI y PDF en la base de datos

        //verificamos q no exista el registro
        $pdfName = $cxc->accountsReceivable_movement . '-folio-' . $cxc->accountsReceivable_movementID . '.pdf';
        $xmlName = $cxc->accountsReceivable_movement . '-folio-' . $cxc->accountsReceivable_movementID . '.xml';
        $guardarPdfCFDI = PROC_ACCOUNTS_RECEIVABLE_FILES::WHERE('accountsReceivableFiles_keyaccountsReceivable', '=', $cxc->accountsReceivable_id)->WHERE('accountsReceivableFiles_file', '=', $pdfName)->first();

        if ($guardarPdfCFDI) {
            $guardarPdfCFDI->accountsReceivableFiles_keyaccountsReceivable = $cxc->accountsReceivable_id;
            $guardarPdfCFDI->accountsReceivableFiles_path = $urlPdfFinal;
            $guardarPdfCFDI->accountsReceivableFiles_file = $pdfName;
            $guardarPdfCFDI->update();
        } else {
            $guardarPdfCFDI = new PROC_ACCOUNTS_RECEIVABLE_FILES();
            $guardarPdfCFDI->accountsReceivableFiles_keyaccountsReceivable = $cxc->accountsReceivable_id;
            $guardarPdfCFDI->accountsReceivableFiles_path = $urlPdfFinal;
            $guardarPdfCFDI->accountsReceivableFiles_file = $pdfName;
            $guardarPdfCFDI->save();
        }

        $guardarXmlCFDI = PROC_ACCOUNTS_RECEIVABLE_FILES::WHERE('accountsReceivableFiles_keyaccountsReceivable', '=', $cxc->accountsReceivable_id)->WHERE('accountsReceivableFiles_file', '=', $xmlName)->first();

        if ($guardarXmlCFDI) {
            $guardarXmlCFDI->accountsReceivableFiles_keyaccountsReceivable = $cxc->accountsReceivable_id;
            $guardarXmlCFDI->accountsReceivableFiles_path = $rutaCFDI;
            $guardarXmlCFDI->accountsReceivableFiles_file = $xmlName;
            $guardarXmlCFDI->update();
        } else {
            $guardarXmlCFDI = new PROC_ACCOUNTS_RECEIVABLE_FILES();
            $guardarXmlCFDI->accountsReceivableFiles_keyaccountsReceivable = $cxc->accountsReceivable_id;
            $guardarXmlCFDI->accountsReceivableFiles_path = $rutaCFDI;
            $guardarXmlCFDI->accountsReceivableFiles_file = $xmlName;
            $guardarXmlCFDI->save();
        }

        //Guardamos los datos correspondientes del CFDI en la base de datos



        $guardarCFDI = PROC_CFDI::WHERE('cfdi_module', '=', "CxC")->WHERE('cfdi_moduleID', '=', $cxc->accountsReceivable_id)->WHERE('cfdi_movementID', '=', $cxc->accountsReceivable_movementID)->WHERE("cfdi_company", '=', $cxc->accountsReceivable_company)->WHERE("cfdi_branchOffice", '=', $cxc->accountsReceivable_branchOffice)->WHERE("cfdi_cancelled", '=', 0)->first();

        if ($guardarCFDI) {
            $guardarCFDI->cfdi_module = 'CxC';
            $guardarCFDI->cfdi_moduleID = $cxc->accountsReceivable_id;
            $guardarCFDI->cfdi_movementID = $cxc->accountsReceivable_movementID;
            $guardarCFDI->cfdi_RFC = $XML->Receptor['Rfc'];
            $guardarCFDI->cfdi_amount = $cxc->accountsReceivable_amount;
            $guardarCFDI->cfdi_taxes = $cxc->accountsReceivable_taxes;
            $guardarCFDI->cfdi_total = $cxc->accountsReceivable_total;
            $guardarCFDI->cfdi_certificateNumber = $XML['NoCertificado'];
            $guardarCFDI->cfdi_stamp = $XML['Sello'];
            $guardarCFDI->cfdi_UUID = $XML->Complemento->TimbreFiscalDigital['UUID'];
            $guardarCFDI->cfdi_Path = $rutaCFDI;
            $guardarCFDI->cfdi_stampSat = $XML->Complemento->TimbreFiscalDigital['SelloSAT'];
            $guardarCFDI->cfdi_certificateNumberSat = $XML->Complemento->TimbreFiscalDigital['NoCertificadoSAT'];
            $guardarCFDI->cfdi_cancelled = 0;
            $guardarCFDI->cfdi_year = Carbon::now()->year;
            $guardarCFDI->cfdi_period = Carbon::now()->month;
            $guardarCFDI->cfdi_money = $XML['Moneda'];
            $guardarCFDI->cfdi_typeChange = $XML['TipoCambio'];
            $guardarCFDI->cfdi_company = $cxc->accountsReceivable_company;
            $guardarCFDI->cfdi_branchOffice = $cxc->accountsReceivable_branchOffice;
            $guardarCFDI->cfdi_Pdf = 1;
            $guardarCFDI->cfdi_document = $CFDI_XML;


            $guardarCFDI->update();
        } else {
            $guardarCFDI = new PROC_CFDI();
            $guardarCFDI->cfdi_module = 'CxC';
            $guardarCFDI->cfdi_moduleID = $cxc->accountsReceivable_id;
            $guardarCFDI->cfdi_movementID = $cxc->accountsReceivable_movementID;
            $guardarCFDI->cfdi_RFC = $XML->Receptor['Rfc'];
            $guardarCFDI->cfdi_amount = $cxc->accountsReceivable_amount;
            $guardarCFDI->cfdi_taxes = $cxc->accountsReceivable_taxes;
            $guardarCFDI->cfdi_total = $cxc->accountsReceivable_total;
            $guardarCFDI->cfdi_certificateNumber = $XML['NoCertificado'];
            $guardarCFDI->cfdi_stamp = $XML['Sello'];
            $guardarCFDI->cfdi_UUID = $XML->Complemento->TimbreFiscalDigital['UUID'];
            $guardarCFDI->cfdi_Path = $rutaCFDI;
            $guardarCFDI->cfdi_stampSat = $XML->Complemento->TimbreFiscalDigital['SelloSAT'];
            $guardarCFDI->cfdi_certificateNumberSat = $XML->Complemento->TimbreFiscalDigital['NoCertificadoSAT'];
            $guardarCFDI->cfdi_cancelled = 0;
            $guardarCFDI->cfdi_year = Carbon::now()->year;
            $guardarCFDI->cfdi_period = Carbon::now()->month;
            $guardarCFDI->cfdi_money = $XML['Moneda'];
            $guardarCFDI->cfdi_typeChange = $XML['TipoCambio'];
            $guardarCFDI->cfdi_company = $cxc->accountsReceivable_company;
            $guardarCFDI->cfdi_branchOffice = $cxc->accountsReceivable_branchOffice;
            $guardarCFDI->cfdi_Pdf = 1;
            $guardarCFDI->cfdi_document = $CFDI_XML;

            $guardarCFDI->save();
        }


        if ($cxc->accountsReceivable_movement === "Aplicación" || $cxc->accountsReceivable_movement === "Cobro de Facturas") {

            //Encontramos las facturas
            $facturas = PROC_ACCOUNTS_RECEIVABLE_DETAILS::where('accountsReceivableDetails_accountReceivableID', $cxc->accountsReceivable_id)->where('accountsReceivableDetails_branchOffice', '=', $cxc->accountsReceivable_branchOffice)->WHERE('accountsReceivableDetails_company', '=', $cxc->accountsReceivable_company)->get();

            foreach ($facturas as $factura) {
                $cxFactura = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_id', $factura->accountsReceivableDetails_movReference)->where('accountsReceivable_branchOffice', '=', $factura->accountsReceivableDetails_branchOffice)->WHERE('accountsReceivable_company', '=', $factura->accountsReceivableDetails_company)->first();

                //revisamos la factura en ventas
                $venta = PROC_SALES::where('sales_movement', $cxFactura->accountsReceivable_origin)->where('sales_movementID', '=', $cxFactura->accountsReceivable_originID)->where('sales_branchOffice', '=', $cxFactura->accountsReceivable_branchOffice)->WHERE('sales_company', '=', $cxFactura->accountsReceivable_company)->where('sales_status', '=', 'FINALIZADO')->first();

                //revismos el uuid de la factura

                $cfdiVentaFactura = PROC_CFDI::where('cfdi_moduleID', '=', $venta->sales_id)->where('cfdi_movementID', '=', $venta->sales_movementID)->WHERE('cfdi_company', '=', $venta->sales_company)->where('cfdi_branchOffice', '=', $venta->sales_branchOffice)->where("cfdi_module", '=', 'Ventas')->select('cfdi_UUID')->first();


                $proc_cfdi_cxc_reference = PROC_CFDI_CXC_REFERENCE::WHERE("cfdiReferenceCxC_cxcID", '=', $cxc->accountsReceivable_id)->WHERE('cfdiReferenceCxC_idOrigin', '=', $venta->sales_id)->first();

                if ($proc_cfdi_cxc_reference) {
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_cxcID = $cxc->accountsReceivable_id;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_module = "CxC";
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_moduleOrigin = "Ventas";
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_idOrigin = $venta->sales_id;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_UUID = $cfdiVentaFactura->cfdi_UUID;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_company = $venta->sales_company;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_branchOffice = $venta->sales_branchOffice;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_move = $cxc->accountsReceivable_movement;
                    $proc_cfdi_cxc_reference->save();
                } else {
                    $proc_cfdi_cxc_reference = new PROC_CFDI_CXC_REFERENCE();
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_cxcID = $cxc->accountsReceivable_id;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_module = "CxC";
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_moduleOrigin = "Ventas";
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_idOrigin = $venta->sales_id;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_UUID = $cfdiVentaFactura->cfdi_UUID;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_company = $venta->sales_company;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_branchOffice = $venta->sales_branchOffice;
                    $proc_cfdi_cxc_reference->cfdiReferenceCxC_move = $cxc->accountsReceivable_movement;
                    $proc_cfdi_cxc_reference->save();
                }




                //calculamos la diferencia de la factura con el cobro
                //Sacamos el balance de la factura
                $detallesFactura = PROC_ACCOUNTS_RECEIVABLE_DETAILS::WHERE("accountsReceivableDetails_apply", '=', $factura->accountsReceivableDetails_apply)->WHERE('accountsReceivableDetails_applyIncrement', '=', $factura->accountsReceivableDetails_applyIncrement)->WHERE('accountsReceivableDetails_company', '=', $factura->accountsReceivableDetails_company)->WHERE('accountsReceivableDetails_branchOffice', '=', $factura->accountsReceivableDetails_branchOffice)->WHERE('accountsReceivableDetails_movReference', '=', $factura->accountsReceivableDetails_movReference)->get();

                $balanceFactura = 0;

                foreach ($detallesFactura as $detalleFactura) {
                    $balanceFactura = $balanceFactura + $detalleFactura->accountsReceivableDetails_amount;
                }

                //sacamos el porcentaje del cobro de la factura
                $balance = $balanceFactura * 100;
                $porcentaje = $balance / $venta->sales_total;





                $guardarCFDI = PROC_CFDI::WHERE('cfdi_module', '=', "CxC")->WHERE('cfdi_moduleID', '=', $cxc->accountsReceivable_id)->WHERE('cfdi_movementID', '=', $cxc->accountsReceivable_movementID)->WHERE("cfdi_company", '=', $cxc->accountsReceivable_company)->WHERE("cfdi_branchOffice", '=', $cxc->accountsReceivable_branchOffice)->WHERE("cfdi_cancelled", '=', 0)->first();


                $proc_cfdi_reference = new PROC_CFDI_REFERENCE();
                $proc_cfdi_reference->cfdiReference_module = "CxC";
                $proc_cfdi_reference->cfdiReference_cfdiID = $guardarCFDI->cfdi_id;
                $proc_cfdi_reference->cfdiReference_moduleOrigin = "Ventas";
                $proc_cfdi_reference->cfdiReference_idOrigin = $venta->sales_id;
                $proc_cfdi_reference->cfdiReference_movementOrigin = $venta->sales_movementID;
                $proc_cfdi_reference->cfdiReference_relationTypeKey = "07";
                $proc_cfdi_reference->cfdiReference_percentage = floatval($porcentaje);
                $proc_cfdi_reference->cfdiReference_company = $venta->sales_company;
                $proc_cfdi_reference->cfdiReference_branchOffice = $venta->sales_branchOffice;
                $proc_cfdi_reference->save();
            }
        }



        $this->enviarEmail($rutaCFDI, $urlPdfFinal, $cliente, $mailCliente, $cxc);
    }

    public function verCorreo()
    {
        return view('page.modulos.Comercial.mail.notificacion');
    }

    //setter and getters
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    }

    public function getMensaje()
    {
        return $this->mensaje;
    }

    //setter and getters
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus2($status)
    {
        $this->statusConexion = $status;
    }

    public function getStatus2()
    {
        return $this->statusConexion;
    }


    public function consultaEstadoCancelacion(Request $request)
    {
        $API_KEY = env('PAC_ADVANCE_KEY');
        $uuid = $request->input('uuid');
        $API_CANCEL_KEY = env('PAC_ADVANCE_WEB_CANCEL');
        // Realiza la consulta del estado para el UUID dado
        $body = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                  <soap:Body>
                    <ConsultarEstadoRequest>
                      <ConsultarEstadoRequest>
                        <Id>' . $uuid . '</Id>
                      </ConsultarEstadoRequest>
                    </ConsultarEstadoRequest>
                  </soap:Body>
                </soap:Envelope>';

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => $API_KEY, 'Cache-Control' => 'no-cache', 'Content-Type' => 'text/xml',
                'SOAPAction' => 'urn:advans-cfdi-cancelacion#ConsultarEstado',
            ])
            ->withOptions(["verify" => false])
            ->withBody($body, 'text/xml')
            ->post($API_CANCEL_KEY);

        if ($response->status() === 200 && !$response->failed()) {

            $company = CAT_COMPANIES::where('companies_key', session('company')->companies_key)->first();
            $company->companies_AvailableStamps = $company->companies_AvailableStamps - 1;
            // dd($company);
            $company->save();

            $historial = new HIST_STAMPED();
            $historial->histStamped_IDCompany = session('company')->companies_key;
            $historial->histStamped_Date = date('Y-m-d H:i:s');
            $historial->histStamped_Stamp = intval(1);
            // dd($historial);
            $historial->save();


            $p = xml_parser_create();
            xml_parse_into_struct($p, $response->body(), $vals, $index);
            xml_parser_free($p);
            dd($vals);

            $estado = $vals[$index['MESSAGE'][0]]['value'];
            $detalle = $vals[$index['DETAIL'][0]]['value'];
            // dd($estado, $detalle);
            // Procesar la respuesta y extraer los datos relevantes para mostrar
            $consultaResult = [
                'uuid' => $uuid,
                'estado' => $estado,
                'detalle' => $detalle,
            ];
            return response()->json($consultaResult);
        }

        return response()->json(['error' => 'Error al obtener el estado de la consulta']);
    }
}
