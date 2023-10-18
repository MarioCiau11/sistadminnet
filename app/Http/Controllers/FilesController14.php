<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;


class FilesController14 extends Controller
{
    public function __invoke(...$args)
    {
        $lastArgDoc = count($args) -1;
        $pathDirect = "";
        $pathFile = $args[$lastArgDoc];

      for ($i=0; $i < $lastArgDoc ; $i++) { 
         $pathDirect .= $args[$i] . "/";
      }

        abort_if(
            ! Storage::disk('empresas') ->exists($pathDirect),
            404,
            'El archivo no existe',
        );
      
    
        return Storage::disk('empresas')->response($pathDirect . $pathFile);
    }
}
