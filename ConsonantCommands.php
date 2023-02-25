<?php

/**
 * @name ConsonantCommands
 * @main ryun42680\consonantcommands\ConsonantCommands
 * @author ryun42680
 * @version 0.0.1
 * @api 5.0.0
 */

namespace ryun42680\consonantcommands;

use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;

final class ConsonantCommands extends PluginBase {

    private int $counting = 0;

    protected function onEnable(): void {
        $this->getScheduler()->scheduleTask(new ClosureTask(function (): void {
            $converted = [];
            $commandMap = $this->getServer()->getCommandMap();
            foreach ($commandMap->getCommands() as $command) {
                if (!in_array(($label = $command->getLabel()), $converted)) {
                    $converted [] = $label;
                    $aliases = $command->getAliases();
                    $aliases [] = $command->getName();
                    $newAliases = array_filter(array_map(function (string $keyword): string {
                        $choseong = array('ㄱ', 'ㄲ', 'ㄴ', 'ㄷ', 'ㄸ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅃ', 'ㅅ', 'ㅆ', 'ㅇ', 'ㅈ', 'ㅉ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ');
                        $result = '';
                        for ($i = 0; $i < mb_strlen($keyword, 'UTF-8'); $i++) {
                            $charAt = mb_substr($keyword, $i, 1, 'UTF-8');
                            $code = $this->utf8_ord($charAt) - 44032;
                            if ($code > -1 and $code < 11172) {
                                $choseong_idx = $code / 588;
                                $result .= $choseong[(int)$choseong_idx];
                            } else if (in_array($charAt, $choseong)) {
                                $result .= $charAt;
                            }
                        }
                        return $result;
                    }, $aliases), function (string $choseong): bool { return $choseong !== ''; });
                    $command->setAliases(array_merge($aliases, $newAliases));
                    $this->counting += count($newAliases);
                    $commandMap->unregister($command);
                    $commandMap->register(strtolower($this->getName()), $command);
                }
            }
            $this->getLogger()->notice('Load ' . $this->counting . ' new aliases');
        }));
    }

    private function utf8_ord($char): bool|int {
        $len = strlen($char);
        if ($len <= 0) return false;
        $h = ord($char[0]);
        if ($h <= 0x7F) return $h;
        if ($h < 0xC2) return false;
        if ($h <= 0xDF and $len > 1) return ($h & 0x1F) << 6 | (ord($char[1]) & 0x3F);
        if ($h <= 0xEF and $len > 2) return ($h & 0x0F) << 12 | (ord($char[1]) & 0x3F) << 6 | (ord($char[2]) & 0x3F);
        if ($h <= 0xF4 and $len > 3) return ($h & 0x0F) << 18 | (ord($char[1]) & 0x3F) << 12 | (ord($char[2]) & 0x3F) << 6 | (ord($char[3]) & 0x3F);
        return false;
    }
}
