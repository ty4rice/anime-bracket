<?php

namespace Controller {

    use Api;
    use Lib;

    class Typeahead {

        public static function render() {

            $query = Lib\Url::Get('q');
            $bracketId = Lib\Url::GetInt('bracketId');
            $out = Api\MalItem::getNameTypeahead($query, 'character');

            if ($bracketId) {
                $out = array_merge($out, self::_getSimilarCharacters($bracketId, $query));
            }

            // Standardize the output
            $out = self::_standardizeData($out);

            Lib\Display::renderJson($out);

        }

        private static function _getSimilarCharacters($bracketId, $query) {

            $retVal = [];

            // Search for similar entered characters first
            $characters = Api\Character::searchBracketCharacters($query, $bracketId);
            if ($characters && count($characters)) {
                $retVal = $characters;
            } else {
                // Search nominees so that maybe we can prevent another similar character being nominated
                $nominees = Api\Nominee::searchBracketNominees($query, $bracketId);
                if ($nominees && count($nominees)) {
                    $retVal = $nominees;
                }
            }

            return $retVal;
        }

        private static function _standardizeData($out) {
            $retVal = [];

            foreach ($out as $suggestion) {
                $obj = null;
                if ($suggestion instanceof Api\Character || $suggestion instanceof Api\Nominee) {
                    $obj = (object)[
                        'order' => 0, // database items take precedence over MAL
                        'id' => $suggestion->id,
                        'name' => $suggestion->name,
                        'source' => $suggestion->source,
                        'image' => $suggestion->image,
                        'thumb' => $suggestion->image,
                        'verified' => true
                    ];
                } else if ($suggestion instanceof Api\MalItem) {
                    $obj = (object)[
                        'order' => 1,
                        'id' => $suggestion->id,
                        'name' => $suggestion->name,
                        'source' => count($suggestion->sources) ? $suggestion->sources[0]->name : '',
                        'image' => str_replace('t.jpg', '.jpg', $suggestion->pic),
                        'thumb' => $suggestion->pic,
                        'verified' => false
                    ];
                }

                // Dedupe
                if ($obj) {
                    $hash = md5($obj->name) . md5($obj->source);
                    if (!isset($retVal[$hash]) || $obj->order < $retVal[$hash]->order) {
                        $retVal[$hash] = $obj;
                    }
                }

            }

            return array_values($retVal);
        }

    }

}