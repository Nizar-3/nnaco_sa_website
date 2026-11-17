<?php
interface AIServiceInterface {
    /**
     * Generate a response from the AI based on the user's message, 
     * chat history, product catalog, and user actor context.
     * 
     * @param string $userMessage The new message from the user
     * @param array $chatHistory Array of previous messages (e.g., [['role'=>'user', 'content'=>'...']])
     * @param array $productCatalog Array of available products (id, name, price, description)
     * @param array $userContext Context of current user: id, role, name, email
     * 
     * @return array Associative array containing 'reply' (string) and 'recommended_product_ids' (array of integers)
     */
    public function generateResponse($userMessage, $chatHistory, $productCatalog, array $userContext = []);
}
