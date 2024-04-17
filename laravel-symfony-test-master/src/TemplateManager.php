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
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $quote = $data['quote'] ?? null;

        if ($quote) {
            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $usefulObject = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);
            $destination = null;

            if(strpos($text, '[quote:destination_link]') !== false) {
                $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            }

            $containsSummaryHtml = strpos($text, '[quote:summary_html]');
            $containsSummary = strpos($text, '[quote:summary]');

            if ($containsSummaryHtml !== false || $containsSummary !== false) {
                $summaryHtml = $containsSummaryHtml !== false ? Quote::renderHtml($_quoteFromRepository) : null;
                $summaryText = $containsSummary !== false ? Quote::renderText($_quoteFromRepository) : null;

                $text = str_replace('[quote:summary_html]', $summaryHtml, $text);
                $text = str_replace('[quote:summary]', $summaryText, $text);
            }

            if ($destination) {
                $destinationLink = $usefulObject->url . '/' . $destination->countryName . '/quote/' . $_quoteFromRepository->id;
                $text = str_replace('[quote:destination_link]', $destinationLink, $text);
            } else {
                $text = str_replace('[quote:destination_link]', '', $text);
            }

            if (strpos($text, '[quote:destination_name]') !== false) {
                $text = str_replace('[quote:destination_name]', $destinationOfQuote->countryName, $text);
            }
        }

        return $text;
    }
}