<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Override;

class Ifelse implements TemplateItem
{
    public $name = '';

    public $bool = true;

    public function __construct($name, $bool = true)
    {
        $this->name = $name;
        $this->bool = $bool;
    }

    #[Override]
    public function run(&$data)
    {
        $preg = "~{{ ?(?<negate>[\!])?if=['\"]{$this->name}['\"] ?}}(?<ifcontent>.*)?(?<elseblock>{{ ?else=['\"]{$this->name}['\"] ?}}(?<elsecontent>.*)?)?{{ ?/if=['\"]{$this->name}['\"] ?}}~sU";
        preg_match_all($preg, (string) $data, $snipped);

        // this could be multiple occurences, hence, we have to cycle though!
        for ($i = 0, $count = count($snipped['ifcontent']); $i < $count; ++$i) {
            $bool = (isset($snipped['negate'][$i]) && $snipped['negate'][$i] === '!') ? !$this->bool : $this->bool;

            if ($bool) {
                $buf = $snipped['ifcontent'][$i];
            } else {
                if (isset($snipped['elseblock'][$i], $snipped['elsecontent'][$i]) && !empty($snipped['elseblock'][$i])) {
                    $buf = $snipped['elsecontent'][$i];
                } else {
                    $buf = '';
                }
            }

            // find occurence, and replace only that one!
            $position = strpos((string) $data, (string) $snipped[0][$i]);
            if ($position !== false) {
                $data = substr_replace((string) $data, (string) $buf, $position, strlen((string) $snipped[0][$i]));
            }
        }
    }
}
