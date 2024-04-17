<?php

namespace App\Services;

use App\src\Entity\Template;
use App\src\Entity\Quote;
use App\src\Entity\User;

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->replaceTextVariables($replaced->subject, $data);
        $replaced->content = $this->replaceTextVariables($replaced->content, $data);

        return $replaced;
    }

    private function replaceTextVariables($text, array $data)
    {
        $text = $this->replaceQuoteVariables($text, $data);
        $text = $this->replaceUserVariables($text, $data);

        return $text;
    }

    private function replaceQuoteVariables($text, array $data)
    {
        if (!isset($data['quote']) || !($data['quote'] instanceof Quote)) {
            return $text;
        }

        $quote = $data['quote'];
        $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
        $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
        $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

        if (strpos($text, '[quote:destination_link]') !== false) {
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
        }

        if (strpos($text, '[quote:summary_html]') !== false) {
            $text = str_replace('[quote:summary_html]', Quote::renderHtml($_quoteFromRepository), $text);
        }

        if (strpos($text, '[quote:summary]') !== false) {
            $text = str_replace('[quote:summary]', Quote::renderText($_quoteFromRepository), $text);
        }

        if (isset($destination)) {
            $text = str_replace('[quote:destination_link]', $usefulObject->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id, $text);
        } else {
            $text = str_replace('[quote:destination_link]', '', $text);
        }

        if (strpos($text, '[quote:destination_name]') !== false) {
            $text = str_replace('[quote:destination_name]', $destinationOfQuote->countryName, $text);
        }

        return $text;
    }

    private function replaceUserVariables($text, array $data)
    {
        if (!isset($data['user']) || !($data['user'] instanceof User)) {
            return $text;
        }

        $_user = $data['user'];

        if (strpos($text, '[user:first_name]') !== false) {
            $text = str_replace('[user:first_name]', ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }
}
