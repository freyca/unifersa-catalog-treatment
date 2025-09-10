<?php

namespace App\Services\AI;

use OpenAI;
use OpenAI\Client;

class OpenAIService implements AIService
{
    private string $AI_model;

    private string $open_ai_key;

    private Client $open_ai_client;

    private string $pre_prompt_template = 'Piensa como si fueras el vendedor de una ferretería. '.
        'Este es el nombre de un artículo a la venta en dicha ferretería: {{DESCRIPCION}}. '.
        'Estas son sus características: {{CARACTERISTICAS}}. '.
        'Esta es la familia a la que pertenece {{FAMILIA}}. ';

    private string $post_prompt = 'Los párrafos deben ser cortos. '.
        'Cuando escribas el nombre del artículo, no uses todo letras mayúsculas, solamente cuando sea necesario. '.
        'Las listas pueden tener varios niveles de sangrado. '.
        'Usa negrita para los títulos de las listas. '.
        'La salida debe ser en formato HTML. '.
        'MUY IMPORTANTE: No uses en markdown, solo texto plano. '.
        'La salida debe ser proporcionada sin saltos de línea. '.
        'Los párrafos deben ir en una etiqueta "p". '.
        'Si usas etiquetas "h" adicionales a la solicitada deben ser "h5". '.
        'No utilices la etiqueta <html> ni <body>. '.
        'Cíñete a los datos proporcionados, los textos que proporciones se incrustarán en una web sin supervisión. '.
        'No incluyas nada extra a lo que se ha solicitado. ';

    private string $meta_texts_prompt = 'Genera un texto json con las claves meta_title y meta_description. '.
        'El límite de meta_title es de 70 caracteres. '.
        'El límite de meta_description es de 155 caracteres y no puede contener caracteres como ">", "<" o "=", intenta usar solo texto. '.
        'Usa iconos para SEO. No abuses de iconos. Son preferibles al inicio del texto. '.
        'Pon espacios entre los iconos y las letras. '.
        'No incluyas saltos de línea. '.
        'No formatees en markdown, solo texto. '.
        'No incluyas nada extra a lo que se ha solicitado. ';

    private string $short_description_prompt = 'Con los datos proporcionados haz una descripción corta del producto que consista exactamente en: '.
        'Un párrafo de entrada con información básica sobre el producto. '.
        'Una ficha técnica resumida en forma de lista. '.
        'Un párrafo final con una llamada a la acción. '.
        'EL texto no puede exceder los 780 caracteres.';

    private string $long_description_prompt = 'Con los datos proporcionados haz una descripción larga del producto que consista exactamente en: '.
        'Una etiqueta "h4" en negrita con el nombre del producto. '.
        'Entre 3 y 5 párrafos hablando del producto. '.
        'Una ficha técnica completa y bien descrita. '.
        'Una lista con ejemplos de uso. ';

    private string $pre_prompt;

    private string $meta_title;

    private string $meta_description;

    public function __construct(string $description, string $features, string $family)
    {
        $this->open_ai_key = config('custom.open_ai_key');
        $this->AI_model = config('custom.open_ai_model');
        $this->open_ai_client = OpenAI::client($this->open_ai_key);

        $this->substituteTextsInPreText($description, $features, $family);
    }

    public function shortDescription(): string
    {
        try {
            $prompt = $this->pre_prompt;
            $prompt .= $this->short_description_prompt;
            $prompt .= $this->post_prompt;

            return $this->callAI($prompt);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function longDescription(): string
    {
        try {
            $prompt = $this->pre_prompt;
            $prompt .= $this->long_description_prompt;
            $prompt .= $this->post_prompt;

            return $this->callAI($prompt);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function metaTitle(): string
    {
        if (isset($this->meta_title)) {
            return $this->meta_title;
        }

        $this->generateMetaTexts();

        return $this->meta_title;
    }

    public function metaDescription(): string
    {
        if (isset($this->meta_descritpion)) {
            return $this->meta_description;
        }

        $this->generateMetaTexts();

        return $this->meta_description;
    }

    private function generateMetaTexts(): void
    {
        try {
            $prompt = $this->pre_prompt;
            $prompt .= $this->meta_texts_prompt;

            $meta_texts = json_decode($this->callAI($prompt));

            $this->meta_title = $meta_texts->meta_title;
            $this->meta_description = $meta_texts->meta_description;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function callAI(string $prompt): string
    {
        try {
            $result = $this->open_ai_client->chat()->create([
                'model' => $this->AI_model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            return $result->choices[0]->message->content;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function substituteTextsInPreText(
        string $description,
        string $features,
        string $familia,
    ): void {
        $pre_prompt = $this->pre_prompt_template;

        $pre_prompt = str_replace('{{DESCRIPCION}}', $description, $pre_prompt);
        $pre_prompt = str_replace('{{CARACTERISTICAS}}', $features, $pre_prompt);
        $pre_prompt = str_replace('{{FAMILIA}}', $familia, $pre_prompt);

        $this->pre_prompt = $pre_prompt;
    }
}
