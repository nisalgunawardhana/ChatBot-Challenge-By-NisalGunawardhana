<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    // Function to show the chat interface
    public function showChat()
    {
        return view('admin.chatbot.index');
    }

    // Function to get the chat response from the OpenAI API
    public function getChatResponse(Request $request)
    {
        // Create a new Guzzle HTTP client
        $client = new Client();
        
        // Send a POST request to the OpenAI API
        $response = $client->post(config('services.openai.api_url'), [
            'json' => [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'You are an AI assistant that helps Provide users with real-time sales forecasts based on criteria like date ranges, product categories, and marketing campaigns. Use the latest sales data and trends to predict demand accurately. Offer insights on attributes like popular colors and clothing types expected to drive sales, helping users make informed decisions on inventory and marketing strategies.'
                            ]
                        ]
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $request->input('message')
                            ]
                        ]
                    ]
                ],
                'temperature' => 0.7,
                'top_p' => 0.95,
                'max_tokens' => 800
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'api-key' =>  config('services.openai.api_key')
            ]
        ]);

        // Get the response body and decode it from JSON
        $body = $response->getBody();
        $data = json_decode($body, true);

        // Return the response as JSON
        return response()->json($data);
    }

    // Function to check the health of the OpenAI API
    public function checkHealth(Request $request)
    {
        // Create a new Guzzle HTTP client
        $client = new Client();
        
        try {
            // Send a POST request to the OpenAI API
            $response = $client->post(config('services.openai.api_url'), [
                'json' => [
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'You are an AI assistant that helps Provide users with real-time sales forecasts based on criteria like date ranges, product categories, and marketing campaigns. Use the latest sales data and trends to predict demand accurately. Offer insights on attributes like popular colors and clothing types expected to drive sales, helping users make informed decisions on inventory and marketing strategies.'
                                ]
                            ]
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => $request->input('message')
                                ]
                            ]
                        ]
                    ],
                    'temperature' => 0.7,
                    'top_p' => 0.95,
                    'max_tokens' => 800
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'api-key' =>  config('services.openai.api_key')
                ]
            ]);

            // Return response code
            return response()->json($response->getStatusCode());
        } catch (\Exception $e) {
            // Handle exception and return error status code
            return response()->json(['error' => 'Failed to check health', 'status' => 500], 500);
        }
    }
}
