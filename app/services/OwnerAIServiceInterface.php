<?php
interface OwnerAIServiceInterface {
    /**
     * Analyze business question based on actual pre-fetched data
     * 
     * @param string $question The question from the owner
     * @param array $businessData The pre-fetched business data from ReportModel
     * @return string The AI's answer in text/HTML format
     */
    public function analyzeBusinessQuery($question, $businessData);
}
