<?php

/*
 * You can place your custom package configuration in here.
 */
return [

    /*
     * Metadatos e identidad que se estampan en los PDF de los DTE.
     * El proyecto padre puede sobreescribir estos valores desde su .env;
     * si no define nada, se usan los valores por defecto (ARISA).
     */
    'pdf' => [
        'title'   => env('DTE_PDF_TITLE', 'ARISA'),
        'author'  => env('DTE_PDF_AUTHOR', 'ARISA - https://www.arisa.cl'),
        'creator' => env('DTE_PDF_CREATOR', 'ARISA - https://www.arisa.cl'),
        'footer'  => env('DTE_PDF_FOOTER', 'ARISA - https://www.arisa.cl'),
    ],

];
