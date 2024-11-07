<?php

namespace App\Http\Controllers;

use LINE\Clients\MessagingApi\Api\MessagingApiApi;
use LINE\Clients\MessagingApi\Model\ReplyMessageRequest;
use LINE\Clients\MessagingApi\Model\TextMessage;
use LINE\Constants\HTTPHeader;
use LINE\Parser\EventRequestParser;
use LINE\Parser\Exception\InvalidEventRequestException;
use LINE\Parser\Exception\InvalidSignatureException;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use Psr\Log\LoggerInterface;

class BotController extends Controller
{
    private MessagingApiApi $bot;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $client = new \GuzzleHttp\Client();
        $config = new \LINE\Clients\MessagingApi\Configuration();
        $config->setAccessToken(env('LINE_CHANNEL_ACCESS_TOKEN'));
        $this->bot = new \LINE\Clients\MessagingApi\Api\MessagingApiApi(
            client: $client,
            config: $config,
        );
    }

    public function handleWebhook()
    {
        $signature = request()->header(HttpHeader::LINE_SIGNATURE);
        if (empty($signature)) {
            return response('Bad Request', 400);
        }

        $parsedEvents = null;
        try {
            $secret = env('LINE_CHANNEL_SECRET');
            $parsedEvents = EventRequestParser::parseEventRequest(request()->getContent(), $secret, $signature);
        } catch (InvalidSignatureException $e) {
            return response('Invalid signature', 400);
        } catch (InvalidEventRequestException $e) {
            return response('Invalid event request', 400);
        }

        foreach ($parsedEvents->getEvents() as $event) {
            if (!($event instanceof MessageEvent)) {
                $this->logger->info("Non message event has come");
                continue;
            }

            $message = $event->getMessage();
            if (!($message instanceof TextMessageContent)) {
                $this->logger->info("Non message event has come");
                continue;
            }

            $replyText = $message->getText();
            $this->bot->replyMessage(new ReplyMessageRequest([
                'replyToken' => $event->getReplyToken(),
                'messages' => [
                    (new TextMessage(['text' => $replyText]))->setType('text'),
                ],
            ]));
        }
        return response('OK', 200);
    }
}
