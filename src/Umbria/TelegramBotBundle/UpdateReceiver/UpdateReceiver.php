<?php

namespace Umbria\TelegramBotBundle\UpdateReceiver;

use AnthonyMartin\GeoLocation\GeoLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Parameter;
use Shaygan\TelegramBotApiBundle\TelegramBotApi;
use Shaygan\TelegramBotApiBundle\Type\Update;
use JMS\DiExtraBundle\Annotation as DI;
use Doctrine\DBAL\Types\Type;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use Umbria\OpenApiBundle\Entity\Tourism\Coordinate;

class UpdateReceiver implements UpdateReceiverInterface
{
    /**
     * @var EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    public $em;

    private $config;
    private $telegramBotApi;

    public function __construct(TelegramBotApi $telegramBotApi, $config)
    {
        $this->telegramBotApi = $telegramBotApi;
        $this->config = $config;
    }

    public function handleUpdate(Update $update)
    {
        $arrayOfArraysOfStrings = array(
            array("/hello", "/help", "/about")
        );
        $newKeyboard = new ReplyKeyboardMarkup($arrayOfArraysOfStrings, true, true);
        $message = json_decode(json_encode($update->message), true);

        /*$newArray = array('/hello' => "/hello", '/help' => "/help", '/about' => "/about");
        $newKeyboard = json_encode($newArray, true);*/

        // Controllo se all'interno dell'Umbria
        if (isset($message['location'])) {
            if (($message['location']['latitude'] >= 45 AND $message['location']['latitude'] <= 45.7)
                AND ($message['location']['longitude'] >= 9 AND $message['location']['longitude'] <= 9.5)
            ) {
                $text = "Sei in provincia di Milano";
            } else {
                $text = "Non sei in provincia di Milano";
            }

            $this->telegramBotApi->sendMessage($message['chat']['id'], $text);
        }

        if (isset($message['text'])) {
            switch ($message['text']) {
                case "/about":
                    $text = "UmbriaTourismBot ti permette di ricevere informazioni turistiche. Invia la tua posizione per scoprire tutte le bellezze che la nostra regione ha in serbo per te";
                    break;
                case "/hello":
                    $text = "Ciao " . $message['from']['first_name'] . ". Oggi ti consiglio di visitare la Gola del Bottaccione";
                    break;
                case "/help":
                case "/start":
                    $text = "UmbriaTourismBot ti permette di ricevere informazioni turistiche. Invia la tua posizione per scoprire tutte le bellezze che la nostra regione ha in serbo per te\n\n";
                default :
                    $text .= "Lista comandi:\n";
                    $text .= "/about - Informazioni sul bot\n";
                    $text .= "/help - Visualizzazione comandi disponibili\n";
                    $text .= "/hello - Suggerimenti\n";
                    break;
            }

            $newKeyboardCond = $message['text'];
            if(strcmp($newKeyboardCond, "/start") XOR strcmp($newKeyboardCond, "/help")){
                $this->telegramBotApi->sendMessage($message['chat']['id'], $text, null, false, null, $newKeyboard);
            } else $this->telegramBotApi->sendMessage($message['chat']['id'], $text);
        }

    }

    public function createQuery($lat, $lng, $radius)
    {
        $builder = $this->em->createQueryBuilder()
            ->select('c')
            ->from('UmbriaOpenApiBundle:Tourism\Coordinate', 'c');

        $lat = 43.3513193;
        $lng = 12.575316599999951;
        $radius = 10;
        if ($lat && $lng) {
            $lat = floatval($lat);
            $lng = floatval($lng);
            $radius = floatval($radius);

            $location = GeoLocation::fromDegrees($lat, $lng);
            /** @var GeoLocation[] $bounds */
            /** @noinspection PhpInternalEntityUsedInspection */
            $bounds = $location->boundingCoordinates($radius, 'km');

            // AS HIDDEN consente di definire un campo di ordinamento (usabile in having) senza alterare
            // il risultato restituito né il formato di restituzione!
            $alias = 'HIDDEN distance';
            $builder->select("c, GEO_DISTANCE(:lat, :lng, c.latitude, c.longitude) AS $alias")
                ->andWhere('c.latitude BETWEEN :minLat and :maxLat')
                ->andWhere('c.longitude BETWEEN :minLng and :maxLng')
                ->andWhere('GEO_DISTANCE(:lat, :lng, c.latitude, c.longitude) < :radius')
                ->orderBy('distance');

            // è necessario specificare i tipi dei parametri come INTEGER per evitare che doctrine
            // inserisca gli apici, cosa che impedisce il funzionamento della query.
            $builder->setParameters(new ArrayCollection(array(
                new Parameter('lat', $lat, Type::INTEGER),
                new Parameter('lng', $lng, Type::INTEGER),
                new Parameter('minLat', $bounds[0]->getLatitudeInDegrees(), Type::INTEGER),
                new Parameter('minLng', $bounds[0]->getLongitudeInDegrees(), Type::INTEGER),
                new Parameter('maxLat', $bounds[1]->getLatitudeInDegrees(), Type::INTEGER),
                new Parameter('maxLng', $bounds[1]->getLongitudeInDegrees(), Type::INTEGER),
                new Parameter('radius', $radius, Type::INTEGER),
            )));
        }

        $query = $builder->getQuery();
        $result = $query->getResult();

        /** @var Coordinate $poi */
        foreach($result as $poi){
            if($poi->getAttrattore() != null)
                print_r($poi->getAttrattore()->getDenominazione());
        }
    }

}
