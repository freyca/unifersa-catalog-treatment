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

    /**
     * Files to download from the Unifersa FTP
     */
    'files_to_download_with_its_final_names' => [
        'base_csv_v2.0.zip' => 'productos.csv',
        'UNIFERSA_ES_SOC_BASE_DESCATALOGADOS.csv' => 'descatalogados.csv',
    ],

    /**
     * Export file name
     */
    'export_file_name' => 'unifersa.csv',

];
