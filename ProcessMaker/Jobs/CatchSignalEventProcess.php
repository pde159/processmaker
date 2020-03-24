<?php

namespace ProcessMaker\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use ProcessMaker\Models\Process;
use ProcessMaker\Models\ProcessRequest;

class CatchSignalEventProcess implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable;

    public $eventDefinition;
    public $payload;
    public $processId;
    public $requestId;
    public $signalRef;
    public $throwEvent;
    public $tokenId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($processId, $signalRef, $payload, $throwEvent, $eventDefinition, $tokenId, $requestId)
    {
        $this->eventDefinition = $eventDefinition;
        $this->payload = $payload;
        $this->processId = $processId;
        $this->requestId = $requestId;
        $this->signalRef = $signalRef;
        $this->throwEvent = $throwEvent;
        $this->tokenId = $tokenId;
    }

    public function handle()
    {
        $mainRequest = ProcessRequest::find($this->requestId);
        $definitions = ($mainRequest->processVersion ?? $mainRequest->process)->getDefinitions(true);
        $engine = $definitions->getEngine();
        $throwEvent = $definitions->findElementById($this->throwEvent)->getBpmnElementInstance();
        $eventDefinition = $definitions->findElementById($this->eventDefinition)->getBpmnElementInstance();
        $instance = $engine->loadProcessRequest($mainRequest);
        $token = $instance->getTokens()->find(function ($token) {
            return $token->getId() == $this->tokenId;
        })->item(0);

        $version = Process::find($this->processId)->getLatestVersion();
        $definitions = $version->getDefinitions(true, null, false);
        $engine = $definitions->getEngine();
        $engine->loadProcessDefinitions($definitions);
        $engine->getEventDefinitionBus()->dispatchEventDefinition(
            $throwEvent,
            $eventDefinition,
            $token
        );

        $engine->runToNextState();
    }
}
