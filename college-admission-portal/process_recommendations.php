<?php
// Process questionnaire responses and get AI recommendations
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration
require_once 'config.php';
$GEMINI_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $GEMINI_API_KEY;

// Comprehensive program catalog based on official university strand priorities
$programCatalog = [
    // Agriculture and Fisheries (GAS priority)
    "BS Agriculture" => ["college" => "College of Agriculture and Forestry", "campuses" => ["Lobo"]],
    "BS Forestry" => ["college" => "College of Agriculture and Forestry", "campuses" => ["Lobo"]],
    "BS Fisheries and Aquatic Sciences" => ["college" => "College of Agriculture and Forestry", "campuses" => ["Lobo"]],

    // Architecture and Design (STEM priority)
    "BS Architecture" => ["college" => "College of Architecture, Fine Arts and Design", "campuses" => ["Alangilan"]],
    "BS Interior Design" => ["college" => "College of Architecture, Fine Arts and Design", "campuses" => ["Alangilan"]],
    "Bachelor of Fine Arts and Design - Visual Communication" => ["college" => "College of Architecture, Fine Arts and Design", "campuses" => ["Alangilan"]],

    // Criminal Justice (HUMSS/GAS priority)
    "BS Criminology" => ["college" => "College of Criminal Justice Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu"]],

    // Arts and Sciences
    "BS Psychology" => ["college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"]],
    "BS Development Communication" => ["college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"]],
    "BA Communication" => ["college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"]],
    "BA English Language Studies" => ["college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"]],

    // Science (STEM priority)
    "BS Biology" => ["college" => "College of Science", "campuses" => ["Pablo Borbon"]],
    "BS Chemistry" => ["college" => "College of Science", "campuses" => ["Alangilan"]],
    "BS Mathematics" => ["college" => "College of Science", "campuses" => ["Pablo Borbon"]],

    // Engineering Programs (STEM priority - ALL BS Engineering degrees)
    "BS Aerospace Engineering" => ["college" => "College of Engineering", "campuses" => ["Aboitiz Lima"]],
    "BS Automotive Engineering" => ["college" => "College of Engineering", "campuses" => ["Aboitiz Lima"]],
    "BS Biomedical Engineering" => ["college" => "College of Engineering", "campuses" => ["Aboitiz Lima"]],
    "BS Ceramics Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Chemical Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Civil Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Computer Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Electrical Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Electronics Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Food Engineering" => ["college" => "College of Engineering", "campuses" => ["Aboitiz Lima"]],
    "BS Geodetic Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Geological Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Industrial Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Instrumentation and Control Engineering" => ["college" => "College of Engineering", "campuses" => ["Aboitiz Lima"]],
    "BS Mechanical Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Mechatronics Engineering" => ["college" => "College of Engineering", "campuses" => ["Aboitiz Lima"]],
    "BS Metallurgical Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Naval Architecture and Marine Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Petroleum Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],
    "BS Sanitary Engineering" => ["college" => "College of Engineering", "campuses" => ["Alangilan"]],

    // Engineering Technology (STEM priority)
    "Bachelor of Automotive Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"]],
    "Bachelor of Civil Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"]],
    "Bachelor of Computer Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Malvar","Lipa","Balayan","Aboitiz Lima"]],
    "Bachelor of Drafting Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"]],
    "Bachelor of Electrical Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Malvar","Lipa","Balayan","Aboitiz Lima"]],
    "Bachelor of Electronics Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Malvar","Lipa","Balayan","Aboitiz Lima"]],
    "Bachelor of Food Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar"]],
    "Bachelor of Instrumentation and Control Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Lipa","Balayan"]],
    "Bachelor of Mechanical Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"]],
    "Bachelor of Mechatronics Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar"]],
    "Bachelor of Welding and Fabrication Engineering Technology" => ["college" => "College of Engineering Technology", "campuses" => ["Alangilan"]],

    // Computing and Technology (STEM priority)
    "BS Computer Science" => ["college" => "College of Informatics and Computing Sciences", "campuses" => ["Alangilan"]],
    "BS Information Technology" => ["college" => "College of Informatics and Computing Sciences", "campuses" => ["Alangilan","Malvar","Nasugbu","Lipa","Balayan","Mabini"]],
    "Bachelor of Industrial Technology" => ["college" => "College of Informatics and Computing Sciences", "campuses" => ["Alangilan","Malvar"]],

    // Health and Allied Sciences (STEM priority)
    "BS Nursing" => ["college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon","Nasugbu"]],
    "BS Nutrition and Dietetics" => ["college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon"]],
    "BS Public Health" => ["college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon"]],
    "BS Public Health - Disaster Response" => ["college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon"]],

    // Accountancy and Business (ABM priority)
    "BS Accountancy" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"]],
    "BS Management Accounting" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"]],
    "BS Business Administration" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon","Nasugbu","Lipa"]],
    "BS Entrepreneurship" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon","Nasugbu"]],
    "BS Customs Administration" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"]],
    "BS Applied Economics" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"]],
    "BS Hospitality Management" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Nasugbu","Pablo Borbon"]],
    "BS Tourism Management" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Nasugbu","Pablo Borbon"]],
    "Bachelor of Public Administration" => ["college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"]],

    // Education (HUMSS priority)
    "Bachelor of Early Childhood Education" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon"]],
    "Bachelor of Elementary Education" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Rosario"]],
    "Bachelor of Physical Education" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa","San Juan","Rosario"]],
    "Bachelor of Secondary Education - English" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa","San Juan","Rosario"]],
    "Bachelor of Secondary Education - Filipino" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","San Juan"]],
    "Bachelor of Secondary Education - Mathematics" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa","Rosario"]],
    "Bachelor of Secondary Education - Sciences" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa"]],
    "Bachelor of Secondary Education - Social Studies" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lemery"]],
    "Bachelor of Technical-Vocational Teacher Education - Electronics Technology" => ["college" => "College of Teacher Education", "campuses" => ["Lemery"]],
    "Bachelor of Technical-Vocational Teacher Education - Garments, Fashion and Design" => ["college" => "College of Teacher Education", "campuses" => ["Lemery"]],
    "Bachelor of Technology and Livelihood Education - Home Economics" => ["college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","San Juan","Rosario"]]
];

function getGeminiRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand) {
    global $GEMINI_URL, $programCatalog;

    // Prepare the prompt for Gemini
    $programList = implode(", ", array_keys($programCatalog));

    // Define strand compatibility for better recommendations
    $strandGuidance = "";
    switch (strtolower($studentStrand)) {
        case 'stem':
            $strandGuidance = "STEM students typically pursue science, technology, engineering, and mathematics programs.";
            break;
        case 'abm':
            $strandGuidance = "ABM students typically pursue business, accountancy, and management programs.";
            break;
        case 'humss':
            $strandGuidance = "HUMSS students typically pursue education, communication, and social sciences programs.";
            break;
        case 'gas':
            $strandGuidance = "GAS students have flexibility to pursue various programs.";
            break;
    }

    // Define strict strand compatibility rules
    $strandRules = [
        'stem' => [
            'can_take' => ['engineering', 'architecture', 'design', 'science', 'technology', 'health', 'computing', 'business', 'education', 'communication'],
            'priority' => ['engineering', 'architecture', 'design', 'science', 'technology', 'computing'],
            'exclusive' => ['architecture', 'design'] // These are STEM-exclusive
        ],
        'abm' => [
            'can_take' => ['business', 'accountancy', 'hospitality', 'tourism', 'public_admin', 'communication'],
            'priority' => ['business', 'accountancy', 'hospitality', 'tourism', 'public_admin'],
            'cannot_take' => ['engineering', 'architecture', 'design', 'science'] // Strict restrictions
        ],
        'humss' => [
            'can_take' => ['education', 'psychology', 'communication', 'criminology', 'public_admin', 'business'],
            'priority' => ['education', 'psychology', 'communication', 'criminology'],
            'cannot_take' => ['engineering', 'architecture', 'design', 'science'] // Strict restrictions
        ],
        'gas' => [
            'can_take' => ['nursing', 'health', 'technology', 'education', 'communication', 'criminology', 'agriculture', 'business'],
            'priority' => ['nursing', 'health', 'technology', 'education', 'communication']
        ]
    ];

    $currentStrandRules = $strandRules[strtolower($studentStrand)] ?? $strandRules['gas'];

    $prompt = "Based on a student's preferences, recommend exactly 3 most suitable Batangas State University programs from this list: {$programList}

CRITICAL STRAND COMPATIBILITY RULES FOR {$studentStrand} students:
" . strtoupper($studentStrand) . " students have specific eligibility requirements. {$strandGuidance}

STRICT COMPATIBILITY RULES:
- " . strtoupper($studentStrand) . " students CAN take programs in: " . implode(', ', $currentStrandRules['can_take']) . "
- " . strtoupper($studentStrand) . " students have PRIORITY in: " . implode(', ', $currentStrandRules['priority']) . "
" . (isset($currentStrandRules['exclusive']) ? "- " . strtoupper($studentStrand) . " students have EXCLUSIVE access to: " . implode(', ', $currentStrandRules['exclusive']) . "\n" : "") . "
" . (isset($currentStrandRules['cannot_take']) ? "- " . strtoupper($studentStrand) . " students CANNOT take: " . implode(', ', $currentStrandRules['cannot_take']) . "\n" : "") . "

Student's preferences:
- Work style: {$workStyle}
- Topic interest: {$topicInterest}
- Dream job: {$dreamJob}
- SHS Strand: {$studentStrand}

INSTRUCTIONS:
1. If student mentions arts, design, architecture, or creative work → ONLY recommend Architecture/Design programs if they are STEM strand
2. If student mentions arts, design, architecture, or creative work → For ABM/HUMSS students, recommend communication or business programs instead
3. NEVER recommend Architecture or Design programs to non-STEM students
4. NEVER recommend Engineering programs to ABM or HUMSS students
5. NEVER recommend Science programs (Biology, Chemistry, Math) to ABM or HUMSS students
6. Match STEM work styles with engineering, science, technology, architecture, and design programs
7. Match creative work styles with design/architecture (STEM only) or communication programs (all strands)
8. Match business work styles with business/accountancy programs (ABM priority)
9. Match social work styles with education, psychology, communication programs (HUMSS priority)
10. Match digital work styles with IT, computer science programs (STEM/GAS priority)
11. Consider dream job keywords as strong indicators, but RESPECT strand compatibility above all
12. If interests don't match strand eligibility, recommend the closest compatible alternative

RECOMMENDATION PRIORITY:
1. Programs that match both interests AND strand eligibility
2. Programs that match strand eligibility (even if interests are secondary)
3. Never recommend programs outside strand eligibility

Please respond with ONLY a JSON array of exactly 3 program names that are COMPATIBLE with their {$studentStrand} strand, like this:
[\"Program Name 1\", \"Program Name 2\", \"Program Name 3\"]

Choose programs that best match their interests but ARE DEFINITELY ELIGIBLE for their {$studentStrand} strand. Be specific and choose from the provided list only.";

    // Prepare the request data
    $requestData = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "topK" => 40,
            "topP" => 0.95,
            "maxOutputTokens" => 1024
        ]
    ];

    // Make the API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $GEMINI_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Add timeout

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Check for curl errors
    if ($curlError) {
        error_log("Curl error: " . $curlError);
        return getFallbackRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand);
    }

    if ($httpCode !== 200) {
        error_log("Gemini API error (HTTP $httpCode): " . $response);
        // Fallback to simple recommendation logic
        return getFallbackRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand);
    }

    // Parse the response
    $responseData = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error: " . json_last_error_msg());
        return getFallbackRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand);
    }

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];
        error_log("AI Response: " . $aiResponse);

        // Try to extract JSON from the response - improved regex to handle nested brackets
        preg_match('/\[[^\[\]]*(?:\[[^\[\]]*\][^\[\]]*)*\]/s', $aiResponse, $matches);
        if ($matches) {
            $jsonString = $matches[0];
            error_log("Extracted JSON: " . $jsonString);

            $recommendedPrograms = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error for extracted array: " . json_last_error_msg());
                return getFallbackRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand);
            }

            if (is_array($recommendedPrograms) && count($recommendedPrograms) >= 3) {
                // Validate that programs exist in our catalog
                $validPrograms = [];
                foreach ($recommendedPrograms as $program) {
                    if (isset($programCatalog[$program])) {
                        $validPrograms[] = $program;
                    } else {
                        error_log("Program not found in catalog: " . $program);
                    }
                }
                if (count($validPrograms) >= 3) {
                    error_log("Valid AI recommendations: " . json_encode(array_slice($validPrograms, 0, 3)));
                    return array_slice($validPrograms, 0, 3);
                } else {
                    error_log("Not enough valid programs found: " . count($validPrograms) . " valid out of " . count($recommendedPrograms));
                }
            } else {
                error_log("AI returned invalid array format or less than 3 programs: " . json_encode($recommendedPrograms));
            }
        } else {
            error_log("Could not extract JSON array from AI response");
        }
    } else {
        error_log("Unexpected Gemini API response structure: " . json_encode($responseData));
    }

    // If AI response parsing fails, use fallback
    return getFallbackRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand);
}

function getFallbackRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand) {
    // Fallback logic - only reached for interests that passed initial strand validation
    $recommendations = [];

    $dreamLower = strtolower($dreamJob);
    $workLower = strtolower($workStyle);
    $topicLower = strtolower($topicInterest);
    $strandLower = strtolower($studentStrand);

    // Business-focused (ABM priority, but available to others)
    if (strpos($dreamLower, 'business') !== false || strpos($workLower, 'business') !== false || strpos($topicLower, 'business_finance') !== false) {
        if ($strandLower === 'abm') {
            $recommendations = ["BS Business Administration", "BS Accountancy", "BS Hospitality Management"];
        } else {
            $recommendations = ["BS Business Administration", "BS Entrepreneurship", "BS Hospitality Management"];
        }
    }
    // Technology-focused (STEM/GAS priority)
    elseif (strpos($dreamLower, 'programmer') !== false || strpos($workLower, 'digital') !== false || strpos($topicLower, 'technology') !== false) {
        if ($strandLower === 'stem') {
            $recommendations = ["BS Information Technology", "BS Computer Science", "BS Computer Engineering"];
        } elseif ($strandLower === 'gas') {
            $recommendations = ["BS Information Technology", "Bachelor of Computer Engineering Technology", "Bachelor of Electrical Engineering Technology"];
        } else {
            $recommendations = ["BS Information Technology", "BS Business Administration", "BS Development Communication"];
        }
    }
    // Health-focused (STEM/GAS priority)
    elseif (strpos($dreamLower, 'doctor') !== false || strpos($dreamLower, 'nurse') !== false || strpos($topicLower, 'health_medical') !== false) {
        if ($strandLower === 'stem' || $strandLower === 'gas') {
            $recommendations = ["BS Nursing", "BS Public Health - Disaster Response", "BS Biology"];
        } else {
            $recommendations = ["BS Nursing", "BS Public Health - Disaster Response", "BS Development Communication"];
        }
    }
    // Education-focused (HUMSS/GAS priority)
    elseif (strpos($dreamLower, 'teacher') !== false || strpos($topicLower, 'education') !== false) {
        if ($strandLower === 'humss') {
            $recommendations = ["Bachelor of Elementary Education", "Bachelor of Secondary Education - English", "Bachelor of Early Childhood Education"];
        } elseif ($strandLower === 'gas') {
            $recommendations = ["Bachelor of Elementary Education", "Bachelor of Secondary Education - English", "BS Information Technology"];
        } else {
            $recommendations = ["Bachelor of Elementary Education", "Bachelor of Secondary Education - English", "BS Development Communication"];
        }
    }
    // Communication/Media focused
    elseif (strpos($dreamLower, 'journalist') !== false || strpos($dreamLower, 'communication') !== false || strpos($topicLower, 'social_humanities') !== false) {
        if ($strandLower === 'humss') {
            $recommendations = ["BA Communication", "BA English Language Studies", "BS Development Communication"];
        } else {
            $recommendations = ["BA Communication", "BS Development Communication", "BS Business Administration"];
        }
    }
    // Hospitality/Tourism focused
    elseif (strpos($dreamLower, 'chef') !== false || strpos($dreamLower, 'hotel') !== false || strpos($topicLower, 'hospitality_tourism') !== false) {
        if ($strandLower === 'abm') {
            $recommendations = ["BS Hospitality Management", "BS Tourism Management", "BS Business Administration"];
        } else {
            $recommendations = ["BS Hospitality Management", "BS Tourism Management", "BS Business Administration"];
        }
    }
    // Default based on strand - for unmatched interests
    else {
        $recommendations = getStrandAppropriatePrograms($strandLower);
    }

    return $recommendations;
}

function getStrandAppropriatePrograms($strand) {
    // Return appropriate programs based on strand priority
    switch (strtolower($strand)) {
        case 'stem':
            return ["BS Computer Engineering", "BS Civil Engineering", "BS Information Technology"];
        case 'abm':
            return ["BS Business Administration", "BS Accountancy", "BS Hospitality Management"];
        case 'humss':
            return ["Bachelor of Elementary Education", "BS Psychology", "BS Development Communication"];
        case 'gas':
            return ["BS Information Technology", "BS Business Administration", "BS Nursing"];
        default:
            return ["BS Information Technology", "BS Business Administration", "BS Nursing"];
    }
}

function validateRecommendationsForStrand($recommendedPrograms, $studentStrand) {
    global $programCatalog;

    if (empty($studentStrand)) {
        return true; // Allow if no strand specified
    }

    $strandLower = strtolower($studentStrand);

    // Special validation for Architecture and Design programs - STEM exclusive
    $artsPrograms = ['BS Architecture', 'BS Interior Design', 'Bachelor of Fine Arts and Design - Visual Communication'];
    $engineeringPrograms = [
        'BS Aerospace Engineering', 'BS Automotive Engineering', 'BS Biomedical Engineering',
        'BS Ceramics Engineering', 'BS Chemical Engineering', 'BS Civil Engineering',
        'BS Computer Engineering', 'BS Electrical Engineering', 'BS Electronics Engineering',
        'BS Food Engineering', 'BS Geodetic Engineering', 'BS Geological Engineering',
        'BS Industrial Engineering', 'BS Instrumentation and Control Engineering',
        'BS Mechanical Engineering', 'BS Mechatronics Engineering', 'BS Metallurgical Engineering',
        'BS Naval Architecture and Marine Engineering', 'BS Petroleum Engineering', 'BS Sanitary Engineering'
    ];
    $sciencePrograms = ['BS Biology', 'BS Chemistry', 'BS Mathematics'];

    foreach ($recommendedPrograms as $programName) {
        // Check arts/design programs - only for STEM
        if (in_array($programName, $artsPrograms) && $strandLower !== 'stem') {
            error_log("INVALID: Arts/Design program '$programName' recommended to $studentStrand student. Arts programs are STEM-exclusive.");
            return false;
        }

        // Check engineering programs - only for STEM and GAS (limited)
        if (in_array($programName, $engineeringPrograms) && !in_array($strandLower, ['stem', 'gas'])) {
            error_log("INVALID: Engineering program '$programName' recommended to $studentStrand student. Engineering programs require STEM or GAS strand.");
            return false;
        }

        // Check pure science programs - STEM priority, but GAS can take some
        if (in_array($programName, $sciencePrograms) && !in_array($strandLower, ['stem', 'gas'])) {
            error_log("INVALID: Science program '$programName' recommended to $studentStrand student. Science programs require STEM or GAS strand.");
            return false;
        }
    }

    // Define comprehensive strand compatibility based on university guidelines
    $strandCompatibility = [
        'stem' => [
            // Priority programs for STEM graduates
            'priority' => [
                // Engineering Programs
                'BS Aerospace Engineering', 'BS Automotive Engineering', 'BS Biomedical Engineering',
                'BS Ceramics Engineering', 'BS Chemical Engineering', 'BS Civil Engineering',
                'BS Computer Engineering', 'BS Electrical Engineering', 'BS Electronics Engineering',
                'BS Food Engineering', 'BS Geodetic Engineering', 'BS Geological Engineering',
                'BS Industrial Engineering', 'BS Instrumentation and Control Engineering',
                'BS Mechanical Engineering', 'BS Mechatronics Engineering', 'BS Metallurgical Engineering',
                'BS Naval Architecture and Marine Engineering', 'BS Petroleum Engineering', 'BS Sanitary Engineering',

                // Architecture and Design
                'BS Architecture', 'BS Interior Design', 'Bachelor of Fine Arts and Design - Visual Communication',

                // Hard Sciences
                'BS Biology', 'BS Chemistry', 'BS Mathematics',

                // Computing and Technology
                'BS Computer Science', 'BS Information Technology',

                // Health and Allied Sciences
                'BS Nursing', 'BS Nutrition and Dietetics', 'BS Public Health',

                // Engineering Technology (shared eligibility)
                'Bachelor of Automotive Engineering Technology', 'Bachelor of Civil Engineering Technology',
                'Bachelor of Computer Engineering Technology', 'Bachelor of Drafting Engineering Technology',
                'Bachelor of Electrical Engineering Technology', 'Bachelor of Electronics Engineering Technology',
                'Bachelor of Food Engineering Technology', 'Bachelor of Instrumentation and Control Engineering Technology',
                'Bachelor of Mechanical Engineering Technology', 'Bachelor of Mechatronics Engineering Technology',
                'Bachelor of Welding and Fabrication Engineering Technology'
            ],
            // Also eligible for all ABM, HUMSS, and GAS programs
            'also_eligible' => true
        ],
        'abm' => [
            // Priority programs for ABM graduates
            'priority' => [
                // Accountancy and Business
                'BS Accountancy', 'BS Management Accounting', 'BS Business Administration',
                'BS Entrepreneurship', 'BS Customs Administration', 'BS Applied Economics',

                // Hospitality and Tourism
                'BS Hospitality Management', 'BS Tourism Management',

                // Public Administration
                'Bachelor of Public Administration',

                // Other eligible
                'BS Development Communication'
            ],
            'also_eligible' => false // ABM students have more restricted eligibility
        ],
        'humss' => [
            // Priority programs for HUMSS graduates
            'priority' => [
                // Social Sciences and Communication
                'BS Psychology', 'BS Criminology', 'BA Communication', 'BA English Language Studies',
                'BS Development Communication',

                // Education
                'Bachelor of Early Childhood Education', 'Bachelor of Elementary Education',
                'Bachelor of Secondary Education - English', 'Bachelor of Secondary Education - Filipino',
                'Bachelor of Secondary Education - Mathematics', 'Bachelor of Secondary Education - Sciences',
                'Bachelor of Secondary Education - Social Studies',

                // Technical-Vocational Teacher Education
                'Bachelor of Technical-Vocational Teacher Education - Electronics Technology',
                'Bachelor of Technical-Vocational Teacher Education - Garments, Fashion and Design',

                // Technology and Livelihood Education
                'Bachelor of Technology and Livelihood Education - Home Economics',

                // Public Administration
                'Bachelor of Public Administration'
            ],
            'also_eligible' => false // HUMSS students have more restricted eligibility
        ],
        'gas' => [
            // GAS graduates have flexible eligibility
            'priority' => [
                // Sciences and Health
                'BS Nursing', 'BS Nutrition and Dietetics', 'BS Public Health',

                // Technology and Computing
                'BS Information Technology', 'Bachelor of Industrial Technology',

                // Education and Liberal Arts
                'Bachelor of Elementary Education', 'Bachelor of Secondary Education - English',
                'Bachelor of Secondary Education - Filipino', 'Bachelor of Secondary Education - Mathematics',
                'Bachelor of Secondary Education - Sciences', 'Bachelor of Secondary Education - Social Studies',
                'BA Communication', 'BA English Language Studies', 'BS Criminology',
                'BS Development Communication',

                // Agriculture and Fisheries
                'BS Agriculture', 'BS Forestry', 'BS Fisheries and Aquatic Sciences'
            ],
            'also_eligible' => false // GAS has specific eligible programs
        ]
    ];

    $compatibility = $strandCompatibility[$strandLower] ?? null;

    if (!$compatibility) {
        error_log("Unknown strand: $studentStrand");
        return false;
    }

    foreach ($recommendedPrograms as $programName) {
        $isAllowed = false;

        // Check if program is in priority list for this strand
        if (in_array($programName, $compatibility['priority'])) {
            $isAllowed = true;
        }
        // For STEM students, also check if they're eligible for other programs
        elseif ($compatibility['also_eligible'] && isset($programCatalog[$programName])) {
            // STEM students can take programs from other strands
            $isAllowed = true;
        }

        if (!$isAllowed) {
            error_log("Program '$programName' not eligible for $studentStrand strand. Priority programs: " . implode(', ', $compatibility['priority']));
            return false;
        }
    }

    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentStrand = $_POST['student_strand'] ?? 'gas';
    $workStyle = $_POST['work_style'] ?? '';
    $topicInterest = $_POST['topic_interest'] ?? '';
    $dreamJob = $_POST['dream_job'] ?? '';

    // Validate inputs
    if (empty($workStyle) || empty($topicInterest) || empty($dreamJob)) {
        header('Location: questionnaire.html?error=missing_fields');
        exit;
    }

    // Comprehensive strand compatibility validation
    $dreamLower = strtolower($dreamJob);
    $workLower = strtolower($workStyle);
    $topicLower = strtolower($topicInterest);
    $strandLower = strtolower($studentStrand);

    // Define interests that require specific strands
    $stemExclusiveInterests = [
        // Arts/Design (STEM-exclusive)
        'architect', 'design', 'artist', 'graphic', 'visual', 'creative',
        // Engineering (STEM-exclusive)
        'engineer', 'engineering', 'mechanical', 'electrical', 'civil', 'computer engineering',
        'chemical engineering', 'industrial engineering', 'electronics', 'aerospace',
        'biomedical', 'petroleum', 'ceramics', 'food engineering', 'geodetic', 'geological',
        'instrumentation', 'mechatronics', 'metallurgical', 'naval architecture', 'petroleum',
        'sanitary engineering', 'automotive engineering', 'biomedical engineering',
        // Science (STEM-exclusive)
        'chemist', 'chemistry', 'biologist', 'biology', 'mathematician', 'mathematics',
        'physics', 'research scientist', 'lab', 'laboratory'
    ];

    $abmExclusiveInterests = [
        // Business/Accountancy (ABM-exclusive)
        'accountant', 'auditor', 'cpa', 'business analyst', 'financial analyst',
        'marketing manager', 'sales manager', 'entrepreneur', 'business owner',
        'bank manager', 'investment banker', 'management consultant',
        'customs officer', 'customs administration', 'applied economics'
    ];

    $humssExclusiveInterests = [
        // Education/Social Sciences (HUMSS-exclusive)
        'teacher', 'educator', 'professor', 'school principal', 'guidance counselor',
        'psychologist', 'counselor', 'social worker', 'criminologist',
        'criminology', 'law enforcement', 'police officer',
        'communication specialist', 'journalist', 'public administrator'
    ];

    // Check for incompatible interests
    $incompatibleInterest = null;

    // Check STEM-exclusive interests
    foreach ($stemExclusiveInterests as $interest) {
        if (strpos($dreamLower, $interest) !== false ||
            ($topicLower === 'engineering' && $interest === 'engineering') ||
            ($topicLower === 'science_research' && in_array($interest, ['chemist', 'biologist', 'research scientist'])) ||
            ($topicLower === 'arts_design' && in_array($interest, ['architect', 'design', 'artist']))) {
            if (!in_array($strandLower, ['stem'])) {
                $incompatibleInterest = 'stem_exclusive';
                break;
            }
        }
    }

    // Check ABM-exclusive interests
    if (!$incompatibleInterest) {
        foreach ($abmExclusiveInterests as $interest) {
            if (strpos($dreamLower, $interest) !== false ||
                ($topicLower === 'business_finance' && strpos($interest, 'business') !== false)) {
                if ($strandLower !== 'abm') {
                    $incompatibleInterest = 'abm_exclusive';
                    break;
                }
            }
        }
    }

    // Check HUMSS-exclusive interests
    if (!$incompatibleInterest) {
        foreach ($humssExclusiveInterests as $interest) {
            if (strpos($dreamLower, $interest) !== false ||
                ($topicLower === 'education' && strpos($interest, 'teacher') !== false) ||
                ($topicLower === 'social_humanities' && strpos($interest, 'psychologist') !== false) ||
                ($topicLower === 'law_criminology' && strpos($interest, 'criminologist') !== false)) {
                if ($strandLower !== 'humss') {
                    $incompatibleInterest = 'humss_exclusive';
                    break;
                }
            }
        }
    }

    // Show error for incompatible interests
    if ($incompatibleInterest) {
        error_log("$incompatibleInterest interest detected for incompatible strand: $studentStrand");

        $errorType = '';
        $exclusiveStrand = '';
        $programExamples = '';

        switch ($incompatibleInterest) {
            case 'stem_exclusive':
                $errorType = 'stem_strand_mismatch';
                $exclusiveStrand = 'STEM';
                $programExamples = 'Engineering, Architecture, Design, or Science programs';
                break;
            case 'abm_exclusive':
                $errorType = 'abm_strand_mismatch';
                $exclusiveStrand = 'ABM';
                $programExamples = 'Business, Accountancy, or Management programs';
                break;
            case 'humss_exclusive':
                $errorType = 'humss_strand_mismatch';
                $exclusiveStrand = 'HUMSS';
                $programExamples = 'Education, Psychology, or Social Science programs';
                break;
        }

        header('Location: questionnaire.html?error=' . $errorType . '&strand=' . urlencode($studentStrand) . '&exclusive=' . urlencode($exclusiveStrand) . '&programs=' . urlencode($programExamples));
        exit;
    }

    // Get AI recommendations
    $recommendedPrograms = getGeminiRecommendations($workStyle, $topicInterest, $dreamJob, $studentStrand);

    // Debug logging
    error_log("AI Recommendations generated: " . json_encode($recommendedPrograms));
    error_log("Student strand: $studentStrand");

    // Validate that recommendations match the student's strand
    $isValidForStrand = validateRecommendationsForStrand($recommendedPrograms, $studentStrand);

    error_log("Strand validation result: " . ($isValidForStrand ? 'VALID' : 'INVALID'));

    if (!$isValidForStrand) {
        // Redirect back to questionnaire with error message
        error_log("AI recommendations don't match student strand. Redirecting back to questionnaire.");
        header('Location: questionnaire.html?error=strand_mismatch&strand=' . urlencode($studentStrand));
        exit;
    }

    // Store recommendations in session for JavaScript to access
    session_start();
    $_SESSION['ai_recommendations'] = $recommendedPrograms;
    $_SESSION['questionnaire_data'] = [
        'work_style' => $workStyle,
        'topic_interest' => $topicInterest,
        'dream_job' => $dreamJob,
        'student_strand' => $studentStrand
    ];


    error_log("Session data stored: " . json_encode($_SESSION));

    // Redirect to success page first
    header('Location: recommendation_success.html');
    exit;
} else {
    // Direct access - redirect to questionnaire
    header('Location: questionnaire.html');
    exit;
}
?>
