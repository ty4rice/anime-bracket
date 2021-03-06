<?php

namespace Controller\Admin {

    use Api;
    use Lib;
    use stdClass;

    class Advance extends \Controller\Me {

        // The amount of time in seconds to delay bracket advancing
        const BRACKET_ADVANCE_DELAY = 300;

        public static function generate(array $params) {

            $message = null;
            $bracket = self::_getBracket(array_shift($params));
            if ($bracket) {

                $cacheKey = 'Controller::Admin::Advance_bracketAdvanceTime_' . $bracket->id;
                $lastBracketAdvance = Lib\Cache::Get($cacheKey);
                if (!$lastBracketAdvance || $lastBracketAdvance + self::BRACKET_ADVANCE_DELAY < time()) {
                    Lib\Cache::Set($cacheKey, time());
                    $bracket->advance();
                    $message = new stdClass;
                    $message->type = 'success';
                    $message->message = $bracket->name . ' has advanced to the next round';
                    self::_refreshCaches($bracket);
                } else {
                    $message = new stdClass;
                    $message->type = 'error';
                    $delta = $lastBracketAdvance + self::BRACKET_ADVANCE_DELAY - time();
                    $time = Lib\Util::relativeTime(time() - $delta);
                    $message->message = $bracket->name . ' was recently advanced. Please wait ' . $time . ' before advancing again.';
                }

            }

            self::_createMessage($message->type, $message->message, true);
            self::_redirectToMain();

        }

    }

}