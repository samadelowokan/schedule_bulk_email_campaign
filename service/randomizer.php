<?php
class Randomizer
{
    public function process($text)
    {
        return preg_replace_callback('/\{(((?>[^\{\}]+)|(?R))*)\}/x', array($this,'replace'), $text );
    }

    public function replace($text)
    {
        $text = $this->process($text[1]);
        $parts = explode('|', $text);
        $part = $parts[array_rand($parts)];
        return $part;
    }
}
