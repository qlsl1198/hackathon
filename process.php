<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// API 키 설정
$api_key = 'OPENAI_API_KEY';

// GPT API 호출 함수
function callGPTAPI($messages) {
    global $api_key;
    
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $data = [
        'model' => 'gpt-4',
        'messages' => $messages,
        'temperature' => 0.7,
        'max_tokens' => 1000
    ];

    // CURL 초기화
    $ch = curl_init($url);
    
    // CURL 옵션 설정
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    
    // API 요청 실행
    $response = curl_exec($ch);
    
    // CURL 오류 체크
    if(curl_errno($ch)) {
        throw new Exception('CURL Error: ' . curl_error($ch));
    }
    
    // CURL 세션 종료
    curl_close($ch);
    
    // 응답 처리
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
    
    if (!isset($result['choices'][0]['message']['content'])) {
        throw new Exception('Invalid API response format');
    }
    
    return $result['choices'][0]['message']['content'];
}

// 응답을 JSON으로 파싱하는 함수
function parseGPTResponse($response) {
    // 원본 응답 로깅
    error_log("Original GPT Response: " . print_r($response, true));

    // 먼저 응답이 이미 JSON 객체인지 확인
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE &&
        isset($decoded['감정상태']) &&
        isset($decoded['답변'])) {
        error_log("Direct JSON parse successful");
        return validateResponse($decoded);
    }

    // JSON 블록 찾기 시도
    if (preg_match('/```json\s*({[\s\S]*?})\s*```/', $response, $matches)) {
        error_log("Found JSON block: " . $matches[1]);
        $decoded = json_decode($matches[1], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            error_log("JSON block parse successful");
            return validateResponse($decoded);
        }
        error_log("JSON block parse failed: " . json_last_error_msg());
    }

    // 일반 JSON 객체 찾기 시도
    if (preg_match('/{[\s\S]*?}/', $response, $matches)) {
        error_log("Found JSON object: " . $matches[0]);
        $decoded = json_decode($matches[0], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            error_log("JSON object parse successful");
            return validateResponse($decoded);
        }
        error_log("JSON object parse failed: " . json_last_error_msg());
    }

    error_log("All JSON parsing attempts failed. Using default response");
    // 파싱 실패시 기본 응답 반환
    return [
        '감정상태' => '분석하는',
        '답변' => cleanResponse($response)
    ];
}

// 응답 데이터 검증 및 정리
function validateResponse($data) {
    error_log("Validating response data: " . print_r($data, true));
    
    // 감정상태 검증
    $validEmotions = ['분노', '만족', '분석하는', '실망한', '광기어린'];
    if (!isset($data['감정상태']) || !in_array($data['감정상태'], $validEmotions)) {
        error_log("Invalid emotion state: " . ($data['감정상태'] ?? 'not set'));
        $data['감정상태'] = '분석하는';
    }

    // 답변 정리
    if (isset($data['답변'])) {
        $data['답변'] = cleanResponse($data['답변']);
    } else {
        error_log("No answer found in response");
        $data['답변'] = '흠... 이건 정말 흥미로운데! 잠시만 기다리게나, 내가 분석해보겠네!';
    }

    error_log("Validated response: " . print_r($data, true));
    return $data;
}

// 응답 텍스트 정리
function cleanResponse($response) {
    error_log("Cleaning response text: " . $response);
    
    // JSON 형식과 코드 블록 마커 제거
    $cleaned = preg_replace('/```json\s*{[\s\S]*?}\s*```/', '', $response);
    $cleaned = preg_replace('/```.*?```/s', '', $cleaned);
    $cleaned = preg_replace('/{[\s\S]*?}/', '', $cleaned);
    
    // 여러 줄의 공백을 하나의 공백으로 변경
    $cleaned = preg_replace('/\s+/', ' ', $cleaned);
    
    // 앞뒤 공백 제거
    $cleaned = trim($cleaned);
    
    error_log("Cleaned response: " . $cleaned);
    
    // 응답이 비어있는 경우 기본 메시지 반환
    return empty($cleaned) ? '흠... 이건 정말 흥미로운데! 잠시만 기다리게나, 내가 분석해보겠네!' : $cleaned;
}

function convertFormDataToText($assetData) {
    $genderText = $assetData['gender'] === 'male' ? '남성' : '여성';
    $incomeTypeText = $assetData['incomeType'] === 'monthly' ? '월급' : '연봉';
    
    $text = "안녕하세요, 저는 {$genderText}이고 직업은 {$assetData['job']}입니다. ";
    $text .= "제 {$incomeTypeText}은 {$assetData['income']}원이고, MBTI는 {$assetData['mbti']}입니다.\n\n";
    
    $text .= "제 지출 내역은 다음과 같습니다:\n";
    foreach ($assetData['expenses'] as $expense) {
        $fixedText = $expense['isFixed'] ? '(고정지출)' : '';
        $text .= "- {$expense['category']}: {$expense['amount']}원 {$fixedText} ({$expense['date']})\n";
    }
    
    return $text;
}

function generateAnalysisPrompt($data) {
    // 고정 지출 합계 계산
    $totalFixed = array_sum($data['fixedExpenses']);
    
    // 변동 지출 합계 계산
    $totalVariable = array_sum($data['variableExpenses']);
    
    // 총 지출
    $totalExpenses = $totalFixed + $totalVariable;
    
    // 월 수입 계산 (연봉인 경우 12로 나눔)
    $monthlyIncome = $data['incomeType'] === 'yearly' ? $data['income'] / 12 : $data['income'];
    
    // 투자 상품 문자열 생성
    $investmentTypes = implode(', ', array_map(function($type) {
        $types = [
            'stocks' => '주식',
            'funds' => '펀드',
            'realestate' => '부동산',
            'crypto' => '암호화폐'
        ];
        return $types[$type] ?? $type;
    }, $data['savings']['investmentTypes']));

    // 대화 기록 불러오기
    $chatHistory = loadChatHistory();
    
    // 프롬프트 시작
    $prompt = "이전 대화 기록:\n";
    foreach ($chatHistory as $chat) {
        if (isset($chat['timestamp']) && isset($chat['userInput'])) {
            $prompt .= date('Y-m-d H:i:s', $chat['timestamp']) . "\n";
            $prompt .= $chat['userInput'] . "\n\n";
        }
    }

    $prompt .= "\n현재 상황:\n";
    $prompt .= "기본 정보:\n";
    $prompt .= "- 성별: " . ($data['gender'] === 'male' ? '남성' : '여성') . "\n";
    $prompt .= "- 직업: {$data['job']}\n";
    $prompt .= "- MBTI: {$data['mbti']}\n";
    $prompt .= "- " . ($data['incomeType'] === 'monthly' ? '월급' : '연봉') . ": {$data['income']}원\n\n";

    $prompt .= "고정 지출 (월):\n";
    $prompt .= "- 주거비: {$data['fixedExpenses']['housing']}원\n";
    $prompt .= "- 공과금: {$data['fixedExpenses']['utilities']}원\n";
    $prompt .= "- 보험료: {$data['fixedExpenses']['insurance']}원\n";
    $prompt .= "- 정기 구독: {$data['fixedExpenses']['subscriptions']}원\n";
    $prompt .= "총 고정 지출: {$totalFixed}원\n\n";

    $prompt .= "변동 지출 (월):\n";
    $prompt .= "- 식비: {$data['variableExpenses']['food']}원\n";
    $prompt .= "- 교통비: {$data['variableExpenses']['transportation']}원\n";
    $prompt .= "- 쇼핑/오락비: {$data['variableExpenses']['entertainment']}원\n";
    $prompt .= "- 비상 비용: {$data['variableExpenses']['emergency']}원\n";
    $prompt .= "총 변동 지출: {$totalVariable}원\n\n";

    $prompt .= "저축/투자:\n";
    $prompt .= "- 월급 대비 저축 비율: {$data['savings']['savingsRatio']}%\n";
    $prompt .= "- 투자 비율: {$data['savings']['investmentRatio']}%\n";
    $prompt .= "- 선호하는 투자 상품: {$investmentTypes}\n\n";

    $prompt .= "종합 분석:\n";
    $prompt .= "- 월 수입: {$monthlyIncome}원\n";
    $prompt .= "- 총 지출: {$totalExpenses}원\n";
    $prompt .= "- 수입 대비 지출 비율: " . round(($totalExpenses / $monthlyIncome) * 100, 1) . "%\n\n";

    $prompt .= "다음 항목들을 포함하여 분석해주세요:\n";
    $prompt .= "1. MBTI 성향을 고려한 소비/투자 패턴 분석\n";
    $prompt .= "2. 현재 지출 구조의 적정성 평가\n";
    $prompt .= "3. 고정 지출 최적화 방안\n";
    $prompt .= "4. 변동 지출 관리 전략\n";
    $prompt .= "5. 저축/투자 포트폴리오 추천\n";
    $prompt .= "6. 장단기 재무 목표 설정\n";
    $prompt .= "7. 구체적인 개선 방안\n\n";

    $prompt .= "실제 숫자를 사용하여 구체적인 조언을 제공해주세요.";

    return $prompt;
}

function loadChatHistory() {
    $historyFile = __DIR__ . '/user_logs.txt';
    if (file_exists($historyFile)) {
        return json_decode(file_get_contents($historyFile), true) ?? [];
    }
    return [];
}

function saveChatHistory($history) {
    $historyFile = __DIR__ . '/user_logs.txt';
    file_put_contents($historyFile, json_encode($history, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

// OPTIONS 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// POST 데이터 받기
$rawData = file_get_contents('php://input');
if (!$rawData) {
    echo json_encode(['error' => '입력 데이터가 없습니다.']);
    exit();
}

try {
    $input = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('잘못된 JSON 형식입니다.');
    }

    // MBTI에 따른 사용자 프로필 이미지 선택
    $userProfile = '장미.jpg'; // 기본값
    if (isset($input['mbti'])) {
        $mbti = $input['mbti'];
        if (in_array($mbti, ['ISTJ', 'ISFJ', 'ISTP', 'ESTJ'])) {
            $userProfile = '장미.jpg';
        } elseif (in_array($mbti, ['ISFP', 'INFP', 'ENFP', 'ESFP'])) {
            $userProfile = '아름.jpg';
        } elseif (in_array($mbti, ['INTJ', 'INTP', 'ENTP', 'ENTJ'])) {
            $userProfile = '코난.jpg';
        } elseif (in_array($mbti, ['ESFJ', 'ENFJ', 'INFJ', 'ENFP'])) {
            $userProfile = '세모.jpg';
        } elseif (in_array($mbti, ['ESTP', 'ESFP', 'ENFP', 'ENTP'])) {
            $userProfile = '뭉치.jpg';
        }

        // 사용자 데이터 저장
        $userDataFile = 'user_data.json';
        $existingData = [];
        if (file_exists($userDataFile)) {
            $existingData = json_decode(file_get_contents($userDataFile), true) ?? [];
        }
        
        // 새 데이터 추가/업데이트
        $existingData['mbti'] = $mbti;
        $existingData['profile'] = $userProfile;
        
        // 데이터 저장
        file_put_contents($userDataFile, json_encode($existingData));
    }

    if (isset($input['action']) && $input['action'] === 'analyzeAssets') {
        if (!isset($input['data'])) {
            throw new Exception('분석할 데이터가 없습니다.');
        }

        // 저장된 시스템 프롬프트 불러오기
        $systemPrompt = file_exists('system_prompt.txt') 
            ? file_get_contents('system_prompt.txt')
            : 'You are a helpful AI assistant.';

        // 자산 데이터를 텍스트로 변환
        $analysisText = generateAnalysisPrompt($input['data']);

        $response = callGPTAPI([
            ['role' => 'system', 'content' => $systemPrompt . "\n" . $analysisText]
        ]);

        // 응답 파싱
        $responseData = parseGPTResponse($response);
        
        // 이미지 번호 매핑
        $emotionImageMap = [
            '분노' => 1,
            '만족' => 2,
            '분석하는' => 3,
            '실망한' => 4,
            '광기어린' => 5
        ];

        $imageNumber = $emotionImageMap[$responseData['감정상태']] ?? 1;

        $finalResponse = [
            'status' => 'success',
            'response' => json_encode([
                '감정상태' => $responseData['감정상태'],
                '답변' => $responseData['답변']
            ], JSON_UNESCAPED_UNICODE),
            'imageNumber' => $imageNumber
        ];
        
        header('Content-Type: application/json');
        echo json_encode($finalResponse);
    } elseif (isset($input['action']) && $input['action'] === 'loadUserData') {
        $userDataFile = __DIR__ . '/user_data.json';
        if (file_exists($userDataFile)) {
            $userData = json_decode(file_get_contents($userDataFile), true);
            $finalResponse = [
                'status' => 'success',
                'data' => $userData
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse, JSON_UNESCAPED_UNICODE);
        } else {
            $finalResponse = [
                'status' => 'error',
                'message' => '저장된 데이터가 없습니다.'
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse);
        }
    } elseif (isset($input['action']) && $input['action'] === 'updatePrompt') {
        if (!isset($input['prompt'])) {
            $finalResponse = [
                'status' => 'error',
                'error' => '프롬프트가 제공되지 않았습니다.'
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse);
            exit;
        }

        try {
            $prompt = $input['prompt'];
            // 프롬프트를 파일에 저장
            file_put_contents('system_prompt.txt', $prompt);
            $finalResponse = [
                'status' => 'success'
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse);
        } catch (Exception $e) {
            $finalResponse = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse);
        }
    } else {
        // 일반 채팅 메시지 처리
        if (!isset($input['message'])) {
            throw new Exception('메시지가 없습니다.');
        }

        $message = trim($input['message']);
        if (empty($message)) {
            throw new Exception('메시지를 입력해주세요.');
        }

        // 시스템 프롬프트 로드
        $systemPrompt = file_exists('system_prompt.txt') 
            ? file_get_contents('system_prompt.txt')
            : 'You are a helpful AI assistant.';

        // GPT 메시지 구성
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "아가사 박사처럼 다음 JSON 형식으로만 응답해주세요 (다른 텍스트나 설명 없이):\n{\"감정상태\": \"분노\"|\"만족\"|\"분석하는\"|\"실망한\"|\"광기어린\", \"답변\": \"아가사 박사의 말투로 작성된 상세한 분석과 조언\"}\n\n사용자 메시지: " . $message]
        ];

        try {
            // GPT API 호출
            $response = callGPTAPI($messages);
            
            // 응답 파싱
            $responseData = parseGPTResponse($response);
            
            // 이미지 번호 매핑
            $emotionImageMap = [
                '분노' => 1,
                '만족' => 2,
                '분석하는' => 3,
                '실망한' => 4,
                '광기어린' => 5
            ];

            $imageNumber = $emotionImageMap[$responseData['감정상태']] ?? 1;

            $finalResponse = [
                'status' => 'success',
                'response' => json_encode([
                    '감정상태' => $responseData['감정상태'],
                    '답변' => $responseData['답변']
                ], JSON_UNESCAPED_UNICODE),
                'imageNumber' => $imageNumber
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse);
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            http_response_code(500);
            $finalResponse = [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
            header('Content-Type: application/json');
            echo json_encode($finalResponse, JSON_UNESCAPED_UNICODE);
        }
    }
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    $finalResponse = [
        'status' => 'error',
        'error' => $e->getMessage()
    ];
    header('Content-Type: application/json');
    echo json_encode($finalResponse, JSON_UNESCAPED_UNICODE);
}
