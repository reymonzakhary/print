<?php

namespace Modules\Cms\Http\Traits;

use Modules\Cms\Compiler\Chunks\ChunkCompiler;
use Modules\Cms\Entities\Chunk;
use Modules\Cms\Entities\Variable;

trait Tags
{

    /**
     * @param string $text
     * @param string $identifier
     * @return array
     */
    public function getDetails(
        string $text = "",
        string $identifier = "*"
    )
    {
        $data = [];
        if ($identifier === "*") {
            $key = str_replace('block.', '', $this->getInbetweenStrings($text, $identifier, "?"));
            if ($variable = optional(Variable::where('key', $key)->select(
                "label", "key", "folder_id", "data_type", "input_type", "default_value",
                "data_variable", "placeholder", "class", "secure_variable", "multi_select",
                "incremental", "min_count", "max_count", "min_size", "max_size", "properties"
            )->first())->toArray()) {

                parse_str(html_entity_decode(str_replace(['`', '"', '&quot;'], ['', '', ''], optional(parse_url($text))['query'])), $params);

                if (isset($params['name'])) {
                    $params['name'] = trim(optional($params)['name']);
                    $data = array_merge($variable, $params);
                }

//                foreach($variable->toArray() as $key=>$value){
//                    if($key === "id"){
//                        $data['variable_id'] = $value;
//                    }else{
//                        dd(optional($params)[$key], $data[$key]= optional($params)[$key]?substr($params[$key],1,-1):$value);
//                        $data[$key]= optional($params)[$key]?substr($params[$key],1,-1):$value;
//                    }
//                }

            }
        } elseif ($identifier === "$") {
            $key = substr($text, 1);
            if ($chunk = Chunk::where('name', $key)->first()) {
                $data = ['name' => $key, 'id' => $chunk->id];
            }
        }
        return $data;
    }

    /**
     * @param string $text
     * @param string $startTag
     * @param string $endTag
     * @return array[]
     */
    public function getTags(
        string $text = "",
        string $startTag = "[[",
        string $endTag = "]]"
    )
    {
        $variablesTags = $this->getInbetweenStrings(ChunkCompiler::flatHtml($text), $startTag, $endTag);
        $ChunksTags = $this->getInbetweenStrings($text, $startTag, $endTag);
        $data = ['variables' => [], 'chunks' => []];
        foreach ($ChunksTags as $i => $tag) {
            if (str_contains($tag, '$')) {
                if ($details = $this->getDetails($tag, '$')) {
                    $data['chunks'][] = ['tag' => $tag, 'details' => $details];
                }
            }
        }
        foreach ($variablesTags as $tag) {
            if (str_contains($tag, '*')) {
                if ($details = $this->getDetails($tag)) {
                    $data['variables'][] = ['tag' => $tag, 'details' => $details];
                }
            }
        }
        return $data;
    }

    /**
     * @param string $text
     * @param string $startTag
     * @param string $endTag
     * @return mixed
     */
    public function getInbetweenStrings(
        string $text = "",
        string $startTag = "[[",
        string $endTag = "]]"
    )
    {
        $delimiter = '#';
        $regex = $delimiter . preg_quote($startTag, $delimiter)
            . '(.*?)'
            . preg_quote($endTag, $delimiter)
            . $delimiter
            . 's';
        preg_match_all($regex, $text, $matches);
        return $matches[1];
    }
}
