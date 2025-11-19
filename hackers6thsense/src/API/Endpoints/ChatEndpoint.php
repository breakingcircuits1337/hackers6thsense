<?php
/**
 * Chat Endpoint with Enhanced Features
 */

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\AI\AIFactory;
use PfSenseAI\AI\ConversationManager;
use PfSenseAI\AI\EnhancedAIResponder;
use PfSenseAI\PfSense\DataCollector;
use PfSenseAI\API\Router;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\ErrorHandler;

class ChatEndpoint extends Router
{
    private $conversationManager;
    private $enhancedResponder;
    private $dataCollector;
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->enhancedResponder = new EnhancedAIResponder();
        $this->dataCollector = new DataCollector();
        $this->errorHandler = new ErrorHandler();
    }

    /**
     * Send message with enhanced features
     */
    public function send()
    {
        try {
            $input = self::getInput();
            
            // Validate message
            Validator::clearErrors();
            $message = Validator::validateQuery($input['message'] ?? null, 2000);
            
            if (empty($message)) {
                Validator::addError('Message is required');
            }
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }
            
            $conversationId = $input['conversation_id'] ?? null;
            $useStreaming = (bool)($input['streaming'] ?? false);
            $includeContext = (bool)($input['include_context'] ?? true);
            $enhancedResponse = (bool)($input['enhanced'] ?? true);

            // Initialize conversation manager
            $this->conversationManager = new ConversationManager($conversationId);

            // Get firewall context if requested
            $firewallContext = $includeContext ? $this->dataCollector->collectMetrics() : [];

            // Handle streaming
            if ($useStreaming) {
                $this->handleStreaming($message, $firewallContext);
                return;
            }

            // Get enhanced response
            if ($enhancedResponse) {
                $response = $this->enhancedResponder->getEnhancedResponse(
                    $message,
                    [],
                    $firewallContext
                );
            } else {
                $aiFactory = AIFactory::getInstance();
                $response = [
                    'response' => $aiFactory->chat($message),
                    'metadata' => [
                        'provider' => $aiFactory->getCurrentProviderName(),
                    ],
                ];
            }

            // Add to conversation history
            $this->conversationManager->addMessage('user', $message);
            $this->conversationManager->addMessage('assistant', $response['response'] ?? $response);

            self::response([
                'status' => 'success',
                'conversation_id' => $this->conversationManager->getConversationId(),
                'message' => $message,
                'response' => $response,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ChatEndpoint::send');
        }
    }

    /**
     * Send multiple messages in conversation
     */
    public function multiTurn()
    {
        try {
            $input = self::getInput();
            $messages = $input['messages'] ?? [];
            $conversationId = $input['conversation_id'] ?? null;

            if (empty($messages)) {
                self::response(['error' => 'Messages array required'], 400);
                return;
            }

            $this->conversationManager = new ConversationManager($conversationId);

            // Get firewall context
            $firewallContext = $this->dataCollector->collectMetrics();

            // Process multiple messages
            $responses = $this->enhancedResponder->getMultiTurnResponse(
                $messages,
                $firewallContext
            );

            // Add all to history
            foreach ($messages as $msg) {
                $this->conversationManager->addMessage('user', $msg);
            }

            self::response([
                'status' => 'success',
                'conversation_id' => $this->conversationManager->getConversationId(),
                'multi_turn' => $responses,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ChatEndpoint::multiTurn');
        }
    }

    /**
     * Get conversation history
     */
    public function getHistory()
    {
        try {
            Validator::clearErrors();
            $conversationId = $_GET['conversation_id'] ?? null;
            $limit = Validator::validateLimit($_GET['limit'] ?? null);
            
            if (empty($conversationId)) {
                Validator::addError('Conversation ID is required');
            }
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $this->conversationManager = new ConversationManager($conversationId);

            self::response([
                'status' => 'success',
                'conversation_id' => $conversationId,
                'history' => $this->conversationManager->getHistory($limit),
                'summary' => $this->conversationManager->getSummary(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ChatEndpoint::getHistory');
        }
    }

    /**
     * Get conversation summary
     */
    public function getSummary()
    {
        try {
            Validator::clearErrors();
            $conversationId = $_GET['conversation_id'] ?? null;
            
            if (empty($conversationId)) {
                Validator::addError('Conversation ID is required');
            }
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $this->conversationManager = new ConversationManager($conversationId);

            self::response([
                'status' => 'success',
                'conversation_id' => $conversationId,
                'summary' => $this->conversationManager->getSummary(),
                'markdown_export' => $this->conversationManager->exportAsMarkdown(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ChatEndpoint::getSummary');
        }
    }

    /**
     * Clear conversation history
     */
    public function clearHistory()
    {
        try {
            $input = self::getInput();
            
            Validator::clearErrors();
            $conversationId = $input['conversation_id'] ?? null;
            
            if (empty($conversationId)) {
                Validator::addError('Conversation ID is required');
            }
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $this->conversationManager = new ConversationManager($conversationId);
            $this->conversationManager->clear();

            self::response([
                'status' => 'success',
                'message' => 'Conversation cleared',
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ChatEndpoint::clearHistory');
        }
    }

    /**
     * Handle streaming response
     */
    private function handleStreaming(string $message, array $firewallContext): void
    {
        $this->enhancedResponder->streamResponse($message, [], function($chunk) {
            // Optional callback for each chunk
        });
    }
}
