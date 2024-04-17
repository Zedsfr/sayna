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
        $quote = $data['quote'] ?? null;

        if (!$quote || !($quote instanceof Quote)) {
            return $text;
        }

        $quoteRepository = QuoteRepository::getInstance();
        $destinationRepository = DestinationRepository::getInstance();
        $siteRepository = SiteRepository::getInstance();

        $quoteId = $quote->id;
        $quoteData = $quoteRepository->getById($quoteId);
        $siteId = $quote->siteId;
        $siteData = $siteRepository->getById($siteId);
        $destinationId = $quote->destinationId;
        $destinationData = $destinationRepository->getById($destinationId);

        $destination = null;

        if (strpos($text, '[quote:destination_link]') !== false) {
            $destination = $destinationRepository->getById($destinationId);
        }

        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        $containsSummary = strpos($text, '[quote:summary]');

        if ($containsSummaryHtml !== false || $containsSummary !== false) {
            $summaryHtml = $containsSummaryHtml !== false ? Quote::renderHtml($quoteData) : null;
            $summaryText = $containsSummary !== false ? Quote::renderText($quoteData) : null;

            $text = str_replace('[quote:summary_html]', $summaryHtml, $text);
            $text = str_replace('[quote:summary]', $summaryText, $text);
        }

        if ($destination) {
            $destinationLink = $siteData->url . '/' . $destinationData->countryName . '/quote/' . $quoteData->id;
            $text = str_replace('[quote:destination_link]', $destinationLink, $text);
        } else {
            $text = str_replace('[quote:destination_link]', '', $text);
        }

        if (strpos($text, '[quote:destination_name]') !== false) {
            $text = str_replace('[quote:destination_name]', $destinationData->countryName, $text);
        }

        return $text;
    }

    private function replaceUserVariables($text, array $data)
    {
        $user = $data['user'] ?? null;

        if (!$user || !($user instanceof User)) {
            return $text;
        }

        $firstName = ucfirst(mb_strtolower($user->firstname));
        $text = str_replace('[user:first_name]', $firstName, $text);

        return $text;
    }
}