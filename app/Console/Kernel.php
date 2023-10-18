<?php

namespace App\Console;

use App\Models\Licenses;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE;
use App\Models\modulos\PROC_ACCOUNTS_PAYABLE_P;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE;
use App\Models\modulos\PROC_ACCOUNTS_RECEIVABLE_P;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;


class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {

            //Buscamos los dias moratorios de las entradas y gastos
            $cxpP = PROC_ACCOUNTS_PAYABLE_P::where('accountsPayableP_status', 'POR AUTORIZAR')->WHERE('accountsPayableP_movement', '=', 'Entrada por Compra')->ORWHERE('accountsPayableP_movement', '=', 'Factura de Gasto')->whereNotNull('accountsPayableP_condition')->get();

            $cxps = PROC_ACCOUNTS_PAYABLE::where('accountsPayable_status', 'POR AUTORIZAR')->WHERE('accountsPayable_movement', '=', 'Entrada por Compra')->ORWHERE('accountsPayable_movement', '=', 'Factura de Gasto')->whereNotNull('accountsPayable_condition')->get();

            //Buscamos los dias moratorios de las facturas
            $cxcP = PROC_ACCOUNTS_RECEIVABLE_P::where('accountsReceivableP_status', 'POR AUTORIZAR')->WHERE('accountsReceivableP_movement', '=', 'Factura')->whereNotNull('accountsReceivableP_condition')->get();

            $cxcs = PROC_ACCOUNTS_RECEIVABLE::where('accountsReceivable_status', 'POR AUTORIZAR')->WHERE('accountsReceivable_movement', '=', 'Factura')->whereNotNull('accountsReceivable_condition')->get();


            //Actualizamos los dias moratorios de las entradas y gastos
            if ($cxpP) {
                foreach ($cxpP as $cxp) {
                    $diasMoratorios = (int) $cxp->accountsPayableP_moratoriumDays;
                    $cxp->accountsPayableP_moratoriumDays = $diasMoratorios + 1;
                    $cxp->update();
                }
            }

            if ($cxps) {
                foreach ($cxps as $cxp2) {
                    $diasMoratorios = (int) $cxp2->accountsPayable_moratoriumDays;
                    $cxp2->accountsPayable_moratoriumDays = $diasMoratorios + 1;
                    $cxp2->update();
                }
            }

            //Actualizamos los dias moratorios de las facturas
            if ($cxcP) {
                foreach ($cxcP as $cxc) {
                    $diasMoratorios = (int) $cxc->accountsReceivableP_moratoriumDays;
                    $cxc->accountsReceivableP_moratoriumDays = $diasMoratorios + 1;
                    $cxc->update();
                }
            }

            if ($cxcs) {
                foreach ($cxcs as $cxc2) {
                    $diasMoratorios = (int) $cxc2->accountsReceivable_moratoriumDays;
                    $cxc2->accountsReceivable_moratoriumDays = $diasMoratorios + 1;
                    $cxc2->update();
                }
            }
        })->daily()->timezone('America/Mexico_City')->onSuccess(function () {
            \Log::info('Se actualizo correctamente los dias moratorios');
        })->onFailure(function () {
            \Log::info('No se actualizo correctamente los dias moratorios');
        })
            ->name('actualizar-dias-moratorios');

        //hacemos que cada 1 minuto se eliminen los datos de la tabla PROC_LICENCES
        $schedule->call(function () {
            $licencias = Licenses::all();
            // dd($licencias);
            //SE BORRA CADA MINUTO SIN IMPORTAR QUE
            foreach ($licencias as $licencia) {
                $licencia->delete();
            }
        })->daily()->timezone('America/Mexico_City')->onSuccess(function () {
            \Log::info('Se eliminaron correctamente los datos de la tabla PROC_LICENCES');
        })->onFailure(function () {
            \Log::info('No se eliminaron correctamente los datos de la tabla PROC_LICENCES');
        })
            ->name('eliminar-licencias');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
