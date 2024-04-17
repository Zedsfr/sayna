<?php

namespace App\Scripts;

require_once __DIR__ . '/../vendor/autoload.php';

use App\src\Entity\Template;
use App\src\Entity\Quote;
use App\src\Entity\User;
use App\src\Repository\TemplateManager;
use Faker\Factory as FakerFactory;

$faker = FakerFactory::create();

$template = new Template(
    1,
    'Your delivery to [quote:destination_name]',
    "
Hi [user:first_name],

Thanks for contacting us to deliver to [quote:destination_name].

Regards,

SAYNA team
");
$templateManager = new TemplateManager();

$message = $templateManager->getTemplateComputed(
    $template,
    [
        'quote' => new Quote($faker->randomNumber(), $faker->randomNumber(), $faker->randomNumber(), $faker->date())
    ]
);

echo $message->subject . "\n" . $message->content;
?>

