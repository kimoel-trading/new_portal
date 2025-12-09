<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Program catalog (same as in programs.js but in PHP format)
$programCatalog = [
    ["program" => "BS Agriculture - Animal Science", "college" => "College of Agriculture and Forestry", "campuses" => ["Lobo"], "strands" => ["gas"]],
    ["program" => "BS Agriculture - Crop Science", "college" => "College of Agriculture and Forestry", "campuses" => ["Lobo"], "strands" => ["gas"]],
    ["program" => "BS Forestry", "college" => "College of Agriculture and Forestry", "campuses" => ["Lobo"], "strands" => ["gas"]],
    ["program" => "BS Architecture", "college" => "College of Architecture, Fine Arts and Design", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Interior Design", "college" => "College of Architecture, Fine Arts and Design", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "Bachelor of Fine Arts and Design - Visual Communication", "college" => "College of Architecture, Fine Arts and Design", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Criminology", "college" => "College of Criminal Justice Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu"], "strands" => ["humss","gas"]],
    ["program" => "BS Psychology", "college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"], "strands" => ["humss"]],
    ["program" => "BS Development Communication", "college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"], "strands" => ["abm","humss","gas"]],
    ["program" => "BA Communication", "college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"], "strands" => ["humss","gas"]],
    ["program" => "BA English Language Studies", "college" => "College of Arts and Sciences", "campuses" => ["Pablo Borbon"], "strands" => ["humss","gas"]],
    ["program" => "BS Biology", "college" => "College of Science", "campuses" => ["Pablo Borbon"], "strands" => []],
    ["program" => "BS Chemistry", "college" => "College of Science", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Mathematics", "college" => "College of Science", "campuses" => ["Pablo Borbon"], "strands" => []],
    ["program" => "BS Aerospace Engineering", "college" => "College of Engineering", "campuses" => ["Aboitiz Lima"], "strands" => []],
    ["program" => "BS Automotive Engineering", "college" => "College of Engineering", "campuses" => ["Aboitiz Lima"], "strands" => []],
    ["program" => "BS Biomedical Engineering", "college" => "College of Engineering", "campuses" => ["Aboitiz Lima"], "strands" => []],
    ["program" => "BS Ceramics Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Chemical Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Civil Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Computer Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Electrical Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Electronics Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Food Engineering", "college" => "College of Engineering", "campuses" => ["Aboitiz Lima"], "strands" => []],
    ["program" => "BS Geodetic Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Geological Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Industrial Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Instrumentation and Control Engineering", "college" => "College of Engineering", "campuses" => ["Aboitiz Lima"], "strands" => []],
    ["program" => "BS Mechanical Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Mechatronics Engineering", "college" => "College of Engineering", "campuses" => ["Aboitiz Lima"], "strands" => []],
    ["program" => "BS Metallurgical Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Naval Architecture and Marine Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Petroleum Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Sanitary Engineering", "college" => "College of Engineering", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "Bachelor of Automotive Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"], "strands" => []],
    ["program" => "Bachelor of Civil Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"], "strands" => []],
    ["program" => "Bachelor of Computer Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Malvar","Lipa","Balayan","Aboitiz Lima"], "strands" => []],
    ["program" => "Bachelor of Drafting Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"], "strands" => []],
    ["program" => "Bachelor of Electrical Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Malvar","Lipa","Balayan","Aboitiz Lima"], "strands" => []],
    ["program" => "Bachelor of Electronics Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Malvar","Lipa","Balayan","Aboitiz Lima"], "strands" => []],
    ["program" => "Bachelor of Food Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar"], "strands" => []],
    ["program" => "Bachelor of Instrumentation and Control Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Lipa","Balayan"], "strands" => []],
    ["program" => "Bachelor of Mechanical Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar","Balayan"], "strands" => []],
    ["program" => "Bachelor of Mechatronics Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan","Malvar"], "strands" => []],
    ["program" => "Bachelor of Welding and Fabrication Engineering Technology", "college" => "College of Engineering Technology", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Computer Science", "college" => "College of Informatics and Computing Sciences", "campuses" => ["Alangilan"], "strands" => []],
    ["program" => "BS Information Technology", "college" => "College of Informatics and Computing Sciences", "campuses" => ["Alangilan","Malvar","Nasugbu","Lipa","Balayan","Mabini"], "strands" => ["gas"]],
    ["program" => "Bachelor of Industrial Technology", "college" => "College of Informatics and Computing Sciences", "campuses" => ["Alangilan","Malvar"], "strands" => ["gas"]],
    ["program" => "BS Nursing", "college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon","Nasugbu"], "strands" => ["gas"]],
    ["program" => "BS Nutrition and Dietetics", "college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon"], "strands" => ["gas"]],
    ["program" => "BS Public Health - Disaster Response", "college" => "College of Nursing and Allied Health Sciences", "campuses" => ["Pablo Borbon"], "strands" => ["gas"]],
    ["program" => "BS Accountancy", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"], "strands" => ["abm"]],
    ["program" => "BS Management Accounting", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"], "strands" => ["abm"]],
    ["program" => "BS Business Administration", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon","Nasugbu","Lipa"], "strands" => ["abm"]],
    ["program" => "BS Entrepreneurship", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon","Nasugbu"], "strands" => ["abm"]],
    ["program" => "BS Customs Administration", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"], "strands" => ["abm"]],
    ["program" => "BS Applied Economics", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"], "strands" => ["abm"]],
    ["program" => "BS Hospitality Management", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Nasugbu","Pablo Borbon"], "strands" => ["abm"]],
    ["program" => "BS Tourism Management", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Nasugbu","Pablo Borbon"], "strands" => ["abm"]],
    ["program" => "Bachelor of Public Administration", "college" => "College of Accountancy, Business and Economics", "campuses" => ["Pablo Borbon"], "strands" => ["abm","humss"]],
    ["program" => "Bachelor of Early Childhood Education", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon"], "strands" => ["humss"]],
    ["program" => "Bachelor of Elementary Education", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Rosario"], "strands" => ["humss","gas"]],
    ["program" => "Bachelor of Physical Education", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa","San Juan","Rosario"], "strands" => ["humss"]],
    ["program" => "Bachelor of Secondary Education - English", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa","San Juan","Rosario"], "strands" => ["humss","gas"]],
    ["program" => "Bachelor of Secondary Education - Filipino", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","San Juan"], "strands" => ["humss","gas"]],
    ["program" => "Bachelor of Secondary Education - Mathematics", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa","Rosario"], "strands" => ["humss","gas"]],
    ["program" => "Bachelor of Secondary Education - Sciences", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lipa"], "strands" => ["humss","gas"]],
    ["program" => "Bachelor of Secondary Education - Social Studies", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","Malvar","Nasugbu","Lemery"], "strands" => ["humss","gas"]],
    ["program" => "Bachelor of Technical-Vocational Teacher Education - Electronics Technology", "college" => "College of Teacher Education", "campuses" => ["Lemery"], "strands" => ["humss"]],
    ["program" => "Bachelor of Technical-Vocational Teacher Education - Garments, Fashion and Design", "college" => "College of Teacher Education", "campuses" => ["Lemery"], "strands" => ["humss"]],
    ["program" => "Bachelor of Technology and Livelihood Education - Home Economics", "college" => "College of Teacher Education", "campuses" => ["Pablo Borbon","San Juan","Rosario"], "strands" => ["humss"]]
];

// Work style mappings
$workStyleMappings = [
    "analytical" => ["stem", "gas"],
    "creative" => ["humss", "abm"],
    "practical" => ["gas", "stem"],
    "theoretical" => ["stem", "gas"],
    "social" => ["humss", "gas"],
    "business" => ["abm", "humss"],
    "digital" => ["gas", "stem"],
    "leadership" => ["humss", "abm"]
];

// Topic interest mappings
$topicMappings = [
    "business_finance" => ["abm"],
    "engineering" => ["stem"],
    "technology" => ["gas"],
    "health_medical" => ["gas"],
    "education" => ["humss"],
    "arts_design" => ["humss"],
    "science_research" => ["stem"],
    "social_humanities" => ["humss"],
    "environment_agriculture" => ["gas"],
    "law_criminology" => ["humss"],
    "hospitality_tourism" => ["abm"],
    "mathematics" => ["stem"],
    "sports_fitness" => ["humss"]
];

function recommendPrograms($studentStrand, $workStyle, $topicInterest, $dreamJob) {
    global $programCatalog, $workStyleMappings, $topicMappings;

    $recommended = [];
    $scores = [];

    // Get preferred strands from work style and topic
    $preferredStrands = [];
    if (isset($workStyleMappings[$workStyle])) {
        $preferredStrands = array_merge($preferredStrands, $workStyleMappings[$workStyle]);
    }
    if (isset($topicMappings[$topicInterest])) {
        $preferredStrands = array_merge($preferredStrands, $topicMappings[$topicInterest]);
    }

    // Add student's actual strand if available
    if ($studentStrand && !in_array($studentStrand, $preferredStrands)) {
        $preferredStrands[] = $studentStrand;
    }

    // Remove duplicates
    $preferredStrands = array_unique($preferredStrands);

    // Score programs based on strand matches
    foreach ($programCatalog as $program) {
        $score = 0;

        // Strand matching
        if (!empty($program['strands'])) {
            foreach ($preferredStrands as $strand) {
                if (in_array($strand, $program['strands'])) {
                    $score += 10; // High score for strand match
                }
            }
        } else {
            // Programs without strand requirements get medium score
            $score += 5;
        }

        // Keyword matching in dream job
        $dreamKeywords = strtolower($dreamJob);
        $programName = strtolower($program['program']);

        $keywords = [
            'engineer' => ['engineering', 'engineer'],
            'teacher' => ['education', 'teacher'],
            'nurse' => ['nursing', 'nurse'],
            'business' => ['business', 'management', 'accountancy'],
            'computer' => ['computer', 'information technology', 'programming'],
            'design' => ['design', 'architecture'],
            'science' => ['science', 'biology', 'chemistry'],
            'agriculture' => ['agriculture', 'forestry'],
            'criminal' => ['criminology', 'criminal justice']
        ];

        foreach ($keywords as $category => $words) {
            foreach ($words as $word) {
                if (strpos($dreamKeywords, $word) !== false && strpos($programName, $word) !== false) {
                    $score += 15; // High bonus for dream job match
                }
            }
        }

        $scores[$program['program']] = $score;
    }

    // Sort by score (highest first)
    arsort($scores);

    // Get top 5 recommendations
    $topPrograms = array_slice(array_keys($scores), 0, 5, true);

    foreach ($topPrograms as $programName) {
        foreach ($programCatalog as $program) {
            if ($program['program'] === $programName) {
                $recommended[] = $program;
                break;
            }
        }
    }

    return $recommended;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentStrand = $_POST['student_strand'] ?? '';
    $workStyle = $_POST['work_style'] ?? '';
    $topicInterest = $_POST['topic_interest'] ?? '';
    $dreamJob = $_POST['dream_job'] ?? '';

    // Validate inputs
    if (empty($workStyle) || empty($topicInterest) || empty($dreamJob)) {
        die("Please fill out all required fields.");
    }

    $recommendations = recommendPrograms($studentStrand, $workStyle, $topicInterest, $dreamJob);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Program Recommendations - Batangas State University</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .content {
            padding: 40px 30px;
        }

        .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .program-card {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .program-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border-color: #4f46e5;
        }

        .program-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
        }

        .program-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .program-college {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .campuses {
            margin-top: 15px;
        }

        .campuses-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }

        .campus-tag {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin: 2px 4px 2px 0;
        }

        .rank-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #10b981;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .back-button {
            display: inline-block;
            background: #6b7280;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 30px;
            font-weight: 500;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background: #4b5563;
        }

        .summary {
            background: #f1f5f9;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid #4f46e5;
        }

        .summary h2 {
            margin: 0 0 15px;
            color: #1e293b;
            font-size: 1.4rem;
        }

        .summary p {
            margin: 5px 0;
            color: #475569;
        }

        .no-recommendations {
            text-align: center;
            padding: 50px 20px;
            color: #64748b;
        }

        .no-recommendations h2 {
            color: #475569;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Your Program Recommendations</h1>
            <p>Based on your preferences and interests, here are the programs that best match you!</p>
        </div>

        <div class="content">
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($recommendations)): ?>

                <div class="summary">
                    <h2>Your Preferences Summary</h2>
                    <p><strong>Work Style:</strong> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $workStyle))); ?></p>
                    <p><strong>Topic Interest:</strong> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $topicInterest))); ?></p>
                    <p><strong>Dream Job:</strong> <?php echo htmlspecialchars($dreamJob); ?></p>
                    <?php if ($studentStrand): ?>
                        <p><strong>Your Strand:</strong> <?php echo htmlspecialchars(strtoupper($studentStrand)); ?></p>
                    <?php endif; ?>
                </div>

                <div class="recommendations-grid">
                    <?php
                    $rank = 1;
                    foreach ($recommendations as $program):
                    ?>
                        <div class="program-card">
                            <div class="rank-badge">#<?php echo $rank; ?></div>
                            <div class="program-title"><?php echo htmlspecialchars($program['program']); ?></div>
                            <div class="program-college"><?php echo htmlspecialchars($program['college']); ?></div>

                            <div class="campuses">
                                <span class="campuses-label">Available Campuses:</span>
                                <?php foreach ($program['campuses'] as $campus): ?>
                                    <span class="campus-tag"><?php echo htmlspecialchars($campus); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php
                    $rank++;
                    endforeach;
                    ?>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <a href="programs.html" class="back-button">← Back to Programs</a>
                    <a href="questionnaire.html" class="back-button" style="margin-left: 15px;">Try Again</a>
                </div>

            <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>

                <div class="no-recommendations">
                    <h2>No Recommendations Found</h2>
                    <p>We couldn't generate recommendations based on your inputs. Please try again with different preferences.</p>
                    <a href="questionnaire.html" class="back-button">Try Again</a>
                </div>

            <?php else: ?>

                <div class="no-recommendations">
                    <h2>Access Denied</h2>
                    <p>Please complete the questionnaire first.</p>
                    <a href="questionnaire.html" class="back-button">Go to Questionnaire</a>
                </div>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
