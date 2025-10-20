<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Open AI configurations
    |--------------------------------------------------------------------------
    */

    'open_ai_key' => env('OPEN_AI_KEY'),

    'open_ai_model' => env('OPEN_AI_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Unifersa configurations
    |--------------------------------------------------------------------------
    */

    'files_to_download' => [
        'base.zip',
        'UNIFERSA_ES_v3.0_SOC_BASE_ARTICULOS.csv',
        'UNIFERSA_ES_v3.0_SOC_BASE_VARIANTES.txt',
        'UNIFERSA_ES_SOC_BASE_DESCATALOGADOS.csv',
    ],

    /**
     * Files to download from the Unifersa FTP
     */
    'files_to_download_with_its_final_names' => [
        'UNIFERSA_ES_v3.0_SOC_BASE_ARTICULOS.csv' => 'productos.csv',
        'UNIFERSA_ES_v3.0_SOC_BASE_VARIANTES.txt' => 'variantes.csv',
        'UNIFERSA_ES_SOC_BASE_DESCATALOGADOS.csv' => 'descatalogados.csv',
        'UNIFERSA_SOC_BASE_FAMILIAS_*' => 'familias.csv',
    ],

    /**
     * Export file name
     */
    'export_file_names' => [
        'productos' => 'productos-procesados',
        'descontinuados' => 'productos-descontinuados.csv',
        'ai_texts' => 'textos-con-ia.csv',
        'variantes' => 'variantes-procesadas.csv',
    ],

];
