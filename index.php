<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Assistant</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Noto Sans KR', sans-serif;
        }

        body {
            background: #e0e5ec;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        #chat-container {
            width: 100%;
            max-width: 1000px;
            background: #e0e5ec;
            border-radius: 20px;
            box-shadow: 20px 20px 60px #bec3c9,
                       -20px -20px 60px #ffffff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 90vh;
            position: relative;
        }

        #asset-form {
            padding: 20px;
            background: #e0e5ec;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            opacity: 1;
            height: auto;
            max-height: calc(90vh - 60px);
            overflow-y: auto;
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            z-index: 1000;
            scrollbar-width: thin;
            scrollbar-color: #bec3c9 #e0e5ec;
            display: block; /* 자산관리 탭 기본으로 열기 */
        }

        #asset-form::-webkit-scrollbar {
            width: 8px;
        }

        #asset-form::-webkit-scrollbar-track {
            background: #e0e5ec;
            border-radius: 4px;
        }

        #asset-form::-webkit-scrollbar-thumb {
            background-color: #bec3c9;
            border-radius: 4px;
            border: 2px solid #e0e5ec;
        }

        #asset-form.hide {
            display: none;
            opacity: 0;
            height: 0;
        }

        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            margin-top: 60px;
            scrollbar-width: thin;
            scrollbar-color: #bec3c9 #e0e5ec;
        }

        #chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: #e0e5ec;
            border-radius: 4px;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background-color: #bec3c9;
            border-radius: 4px;
            border: 2px solid #e0e5ec;
        }

        #toggle-asset-form {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #e0e5ec;
            border: none;
            padding: 12px 25px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        #prompt-edit-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #e0e5ec;
            border: none;
            padding: 12px 25px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
            z-index: 1001;
        }

        #prompt-edit-button:hover {
            box-shadow: 3px 3px 6px #bec3c9,
                       -3px -3px 6px #ffffff;
            transform: translateY(-1px);
        }

        #prompt-edit-button:active {
            box-shadow: inset 2px 2px 5px #bec3c9,
                       inset -2px -2px 5px #ffffff;
            transform: translateY(0);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #e0e5ec;
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 20px 20px 60px #bec3c9,
                       -20px -20px 60px #ffffff;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .modal textarea {
            width: 100%;
            height: 300px;
            margin: 20px 0;
            padding: 15px;
            border: none;
            border-radius: 15px;
            background: #e0e5ec;
            box-shadow: inset 5px 5px 10px #bec3c9,
                       inset -5px -5px 10px #ffffff;
            font-family: 'Noto Sans KR', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
        }

        .modal textarea:focus {
            outline: none;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
        }

        .modal-button {
            background: #e0e5ec;
            border: none;
            padding: 12px 25px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 500;
            color: #666;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
            margin: 0 10px;
        }

        .modal-button:hover {
            box-shadow: 3px 3px 6px #bec3c9,
                       -3px -3px 6px #ffffff;
            transform: translateY(-1px);
        }

        .modal-button:active {
            box-shadow: inset 2px 2px 5px #bec3c9,
                       inset -2px -2px 5px #ffffff;
            transform: translateY(0);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-row {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .asset-input {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 15px;
            font-size: 14px;
            background: #e0e5ec;
            box-shadow: inset 5px 5px 10px #bec3c9,
                       inset -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
        }

        .asset-input:focus {
            outline: none;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
        }

        .asset-button {
            width: 100%;
            padding: 15px;
            margin-top: 20px;
            background: #e0e5ec;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            cursor: pointer;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
        }

        .asset-button:hover {
            box-shadow: 3px 3px 6px #bec3c9,
                       -3px -3px 6px #ffffff;
            transform: translateY(-1px);
        }

        .asset-button:active {
            box-shadow: inset 2px 2px 5px #bec3c9,
                       inset -2px -2px 5px #ffffff;
            transform: translateY(0);
        }

        #chat-header {
            background: #e0e5ec;
            padding: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        #chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        #chat-header h1 {
            font-size: 1.5rem;
            color: #333;
            font-weight: 500;
        }

        #chatbox {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            background: #e0e5ec;
        }

        #chatbox::-webkit-scrollbar {
            width: 6px;
        }

        #chatbox::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #chatbox::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .message {
            max-width: 80%;
            padding: 15px;
            border-radius: 15px;
            position: relative;
            animation: fadeIn 0.3s ease;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .user {
            background: #e0e5ec;
            color: #666;
            margin-left: auto;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
        }

        .bot {
            background: #e0e5ec;
            color: #666;
            margin-right: auto;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
        }

        .typing-indicator {
            display: flex;
            gap: 5px;
            padding: 10px;
            background: #e0e5ec;
            border-radius: 15px;
            width: fit-content;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #e0e5ec;
            border-radius: 50%;
            animation: typing 1s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        #input-container {
            padding: 20px;
            background: #e0e5ec;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        #chatInput {
            width: calc(100% - 70px);
            padding: 15px;
            border: none;
            border-radius: 15px;
            background: #e0e5ec;
            box-shadow: inset 5px 5px 10px #bec3c9,
                       inset -5px -5px 10px #ffffff;
            margin-right: 10px;
            font-size: 14px;
        }

        #chatInput:focus {
            outline: none;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
        }

        #sendButton {
            padding: 15px 30px;
            background: #e0e5ec;
            color: #666;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #sendButton:hover {
            box-shadow: 3px 3px 5px #bec3c9,
                       -3px -3px 5px #ffffff;
            color: #667eea;
        }

        #sendButton:active {
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
        }

        .send-icon {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .expense-table-container {
            max-height: 300px;
            overflow-y: auto;
            margin: 15px 0;
            border-radius: 15px;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
        }

        .expense-table-container::-webkit-scrollbar {
            width: 8px;
        }

        .expense-table-container::-webkit-scrollbar-track {
            background: #e0e5ec;
            border-radius: 4px;
        }

        .expense-table-container::-webkit-scrollbar-thumb {
            background: #bec3c9;
            border-radius: 4px;
        }

        .expense-table {
            width: 100%;
            border-collapse: collapse;
            background: #e0e5ec;
        }

        .expense-table th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #d1d9e6;
        }

        .expense-table th, .expense-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .expense-table input[type="text"],
        .expense-table input[type="number"],
        .expense-table input[type="date"] {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 8px;
            background: #e0e5ec;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
        }

        .expense-table input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 5px;
            cursor: pointer;
        }

        .expense-table select {
            width: 100%;
            padding: 8px;
            border: none;
            border-radius: 8px;
            background: #e0e5ec;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
            appearance: none;
            cursor: pointer;
        }

        .expense-table select:focus {
            outline: none;
            box-shadow: inset 2px 2px 3px #bec3c9,
                       inset -2px -2px 3px #ffffff;
        }

        .delete-button {
            background: #e0e5ec;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 3px 3px 5px #bec3c9,
                       -3px -3px 5px #ffffff;
            transition: all 0.3s ease;
        }

        .delete-button:hover {
            color: #ff4444;
            box-shadow: 2px 2px 3px #bec3c9,
                       -2px -2px 3px #ffffff;
        }

        .delete-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            box-shadow: none;
        }

        .add-row-button {
            margin-top: 10px;
            padding: 8px 15px;
            background: #e0e5ec;
            border: none;
            border-radius: 10px;
            color: #666;
            cursor: pointer;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
            transition: all 0.3s ease;
        }

        .add-row-button:hover {
            box-shadow: 3px 3px 5px #bec3c9,
                       -3px -3px 5px #ffffff;
            color: #667eea;
        }

        .radio-group {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .income-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .income-type-select {
            width: 100px;
            padding: 8px;
            border: none;
            border-radius: 8px;
            background: #e0e5ec;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
            appearance: none;
            cursor: pointer;
        }

        .income-type-select:focus {
            outline: none;
            box-shadow: inset 2px 2px 3px #bec3c9,
                       inset -2px -2px 3px #ffffff;
        }

        .mbti-select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 15px;
            background: #e0e5ec;
            box-shadow: inset 3px 3px 5px #bec3c9,
                       inset -3px -3px 5px #ffffff;
            appearance: none;
            cursor: pointer;
            font-size: 14px;
        }

        .mbti-select:focus {
            outline: none;
            box-shadow: inset 2px 2px 3px #bec3c9,
                       inset -2px -2px 3px #ffffff;
        }

        .expense-section {
            background: #e0e5ec;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 5px 5px 10px #bec3c9,
                       -5px -5px 10px #ffffff;
        }

        .expense-section h3 {
            color: #444;
            margin-bottom: 15px;
            font-size: 1.1em;
        }

        .checkbox-group, .radio-group {
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }

        .checkbox-group label, .radio-group label {
            margin-left: 5px;
            color: #666;
        }

        .income-section {
            background: #e0e5ec;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 8px 8px 15px #a3b1c6, 
                       -8px -8px 15px #ffffff;
        }

        .income-section h3 {
            color: #2d3436;
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: 600;
            text-align: center;
        }

        .income-section .form-group {
            margin-bottom: 15px;
        }

        .income-section input[type="number"],
        .income-section select {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #e0e5ec;
            box-shadow: inset 2px 2px 5px #b8b9be, 
                       inset -3px -3px 7px #ffffff;
            color: #2d3436;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .income-section input[type="number"]:focus,
        .income-section select:focus {
            outline: none;
            box-shadow: inset 1px 1px 2px #b8b9be, 
                       inset -1px -1px 2px #ffffff;
        }

        .income-section .sub-form-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .income-section .sub-form-group input {
            flex: 1;
            min-width: 140px;
        }

        .income-section label {
            display: block;
            margin-bottom: 8px;
            color: #2d3436;
            font-weight: 500;
            font-size: 14px;
        }

        #income_type {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #e0e5ec;
            box-shadow: inset 2px 2px 5px #b8b9be, 
                       inset -3px -3px 7px #ffffff;
            color: #2d3436;
            font-size: 14px;
            cursor: pointer;
        }

        #income_type:hover {
            box-shadow: inset 1px 1px 2px #b8b9be, 
                       inset -1px -1px 2px #ffffff;
        }

        .sub-form-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sub-form-group input {
            flex: 1;
            min-width: 120px;
        }

        /* 재무 목표 섹션 스타일 */
        .financial-goals-section {
            background: #e0e5ec;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 8px 8px 15px #a3b1c6, 
                       -8px -8px 15px #ffffff;
        }

        .financial-goals-section h3 {
            color: #2d3436;
            margin-bottom: 20px;
            font-size: 1.2em;
            font-weight: 600;
            text-align: center;
        }

        .goal-category {
            margin-bottom: 25px;
        }

        .goal-category h4 {
            color: #2d3436;
            margin-bottom: 15px;
            font-size: 1.1em;
            font-weight: 500;
            padding-left: 10px;
            border-left: 4px solid #74b9ff;
        }

        .goal-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .goal-item {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
            padding: 15px;
            background: #e0e5ec;
            border-radius: 10px;
            box-shadow: inset 2px 2px 5px #b8b9be, 
                       inset -3px -3px 7px #ffffff;
        }

        .goal-item input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #e0e5ec;
            box-shadow: inset 2px 2px 5px #b8b9be, 
                       inset -3px -3px 7px #ffffff;
            color: #2d3436;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .goal-item input:focus {
            outline: none;
            box-shadow: inset 1px 1px 2px #b8b9be, 
                       inset -1px -1px 2px #ffffff;
        }

        .add-goal-btn {
            align-self: flex-start;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            background: #e0e5ec;
            color: #2d3436;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 3px 3px 6px #b8b9be, 
                       -3px -3px 6px #ffffff;
            transition: all 0.3s ease;
        }

        .add-goal-btn:hover {
            box-shadow: 2px 2px 4px #b8b9be, 
                       -2px -2px 4px #ffffff;
            transform: translateY(1px);
        }

        @media (max-width: 768px) {
            .goal-item {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            #chat-container {
                height: 100vh;
                border-radius: 0;
            }

            .message {
                max-width: 90%;
            }

            #chat-header h1 {
                font-size: 1.2rem;
            }
        }

        /* 채팅 메시지 컨테이너 스타일 */
        .message-container {
            display: flex;
            align-items: flex-start;
            margin: 20px;
            padding: 15px;
            border-radius: 15px;
            background: #e0e5ec;
            box-shadow: 5px 5px 10px #a3b1c6, 
                       -5px -5px 10px #ffffff;
        }

        /* 사용자 메시지 특별 스타일 */
        .message-container.user-message {
            flex-direction: row-reverse;
            background: #74b9ff;
        }

        .message-container.user-message .message-content {
            color: white;
            margin-right: 0;
            margin-left: 15px;
            background: #74b9ff;
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.2),
                       inset -2px -2px 5px rgba(255, 255, 255, 0.1);
        }

        /* 프로필 이미지 스타일 */
        .chat-profile-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 15px;
            box-shadow: 3px 3px 6px #a3b1c6,
                       -3px -3px 6px #ffffff;
        }

        /* 메시지 내용 스타일 */
        .message-content {
            flex: 1;
            padding: 12px 15px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.5;
            color: #2d3436;
            background: #e0e5ec;
            box-shadow: inset 2px 2px 5px #b8b9be,
                       inset -3px -3px 7px #ffffff;
        }

        /* 채팅 창 스타일 */
        #chat-messages {
            padding: 20px;
            overflow-y: auto;
            background: #e0e5ec;
        }

        /* 스크롤바 스타일 */
        #chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: #e0e5ec;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background: #a3b1c6;
            border-radius: 4px;
        }

        #chat-messages::-webkit-scrollbar-thumb:hover {
            background: #8e9eb3;
        }

        /* 메시지 시간 표시 */
        .message-time {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div id="chat-container">
        <button id="prompt-edit-button">프롬프트 수정</button>
        <button id="toggle-asset-form">자산관리</button>
        <div id="asset-form">
            <div class="form-row">
                <div class="form-group">
                    <label>성별</label>
                    <div class="radio-group">
                        <input type="radio" id="male" name="gender" value="male">
                        <label for="male">남성</label>
                        <input type="radio" id="female" name="gender" value="female">
                        <label for="female">여성</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="job">직업</label>
                    <input type="text" id="job" class="asset-input" placeholder="직업을 입력하세요">
                </div>
            </div>
            
            <div class="income-section">
                <h3>수입 정보</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="salary">월급/연봉</label>
                        <div class="sub-form-group">
                            <input type="number" id="salary_before_tax" name="salary_before_tax" placeholder="세전">
                            <input type="number" id="salary_after_tax" name="salary_after_tax" placeholder="세후">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="income_type">소득 형태</label>
                        <select id="income_type" name="income_type">
                            <option value="fixed">고정(정기적 급여)</option>
                            <option value="variable">비고정(프로젝트/비정기)</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="additional_income">부수입</label>
                        <div class="sub-form-group">
                            <input type="number" id="rental_income" name="rental_income" placeholder="임대소득">
                            <input type="number" id="investment_income" name="investment_income" placeholder="투자소득">
                            <input type="number" id="freelance_income" name="freelance_income" placeholder="프리랜서 수입">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="incomeType">수입 유형</label>
                    <select id="incomeType" class="asset-input">
                        <option value="monthly">월급</option>
                        <option value="yearly">연봉</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="income">수입 금액</label>
                    <input type="number" id="income" class="asset-input" placeholder="금액을 입력하세요">
                </div>
                <div class="form-group">
                    <label for="mbti">MBTI</label>
                    <select id="mbti" class="asset-input">
                        <option value="">선택하세요</option>
                        <option value="ISTJ">ISTJ</option>
                        <option value="ISFJ">ISFJ</option>
                        <option value="INFJ">INFJ</option>
                        <option value="INTJ">INTJ</option>
                        <option value="ISTP">ISTP</option>
                        <option value="ISFP">ISFP</option>
                        <option value="INFP">INFP</option>
                        <option value="INTP">INTP</option>
                        <option value="ESTP">ESTP</option>
                        <option value="ESFP">ESFP</option>
                        <option value="ENFP">ENFP</option>
                        <option value="ENTP">ENTP</option>
                        <option value="ESTJ">ESTJ</option>
                        <option value="ESFJ">ESFJ</option>
                        <option value="ENFJ">ENFJ</option>
                        <option value="ENTJ">ENTJ</option>
                    </select>
                </div>
            </div>

            <div class="financial-goals-section">
                <h3>재무 목표</h3>
                <div class="goal-category">
                    <h4>단기 목표</h4>
                    <div class="goal-items">
                        <div class="goal-item">
                            <input type="text" id="short_term_goal_1" name="short_term_goal_1" placeholder="목표 (예: 여행)">
                            <input type="number" id="short_term_amount_1" name="short_term_amount_1" placeholder="목표 금액">
                            <input type="date" id="short_term_date_1" name="short_term_date_1">
                        </div>
                        <button class="add-goal-btn" data-type="short">+ 단기 목표 추가</button>
                    </div>
                </div>
                <div class="goal-category">
                    <h4>중기 목표</h4>
                    <div class="goal-items">
                        <div class="goal-item">
                            <input type="text" id="mid_term_goal_1" name="mid_term_goal_1" placeholder="목표 (예: 차량 구매)">
                            <input type="number" id="mid_term_amount_1" name="mid_term_amount_1" placeholder="목표 금액">
                            <input type="date" id="mid_term_date_1" name="mid_term_date_1">
                        </div>
                        <button class="add-goal-btn" data-type="mid">+ 중기 목표 추가</button>
                    </div>
                </div>
                <div class="goal-category">
                    <h4>장기 목표</h4>
                    <div class="goal-items">
                        <div class="goal-item">
                            <input type="text" id="long_term_goal_1" name="long_term_goal_1" placeholder="목표 (예: 주택 구매)">
                            <input type="number" id="long_term_amount_1" name="long_term_amount_1" placeholder="목표 금액">
                            <input type="date" id="long_term_date_1" name="long_term_date_1">
                        </div>
                        <button class="add-goal-btn" data-type="long">+ 장기 목표 추가</button>
                    </div>
                </div>
            </div>

            <div class="expense-section">
                <h3>고정 지출</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="housing">주거비 (월세/대출)</label>
                        <input type="number" id="housing" class="asset-input" placeholder="금액">
                    </div>
                    <div class="form-group">
                        <label for="utilities">공과금</label>
                        <input type="number" id="utilities" class="asset-input" placeholder="금액">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="insurance">보험료</label>
                        <input type="number" id="insurance" class="asset-input" placeholder="금액">
                    </div>
                    <div class="form-group">
                        <label for="subscriptions">정기 구독</label>
                        <input type="number" id="subscriptions" class="asset-input" placeholder="금액">
                    </div>
                </div>
            </div>

            <div class="expense-section">
                <h3>변동 지출</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="food">식비</label>
                        <input type="number" id="food" class="asset-input" placeholder="금액">
                    </div>
                    <div class="form-group">
                        <label for="transportation">교통비</label>
                        <input type="number" id="transportation" class="asset-input" placeholder="금액">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="entertainment">쇼핑/오락비</label>
                        <input type="number" id="entertainment" class="asset-input" placeholder="금액">
                    </div>
                    <div class="form-group">
                        <label for="emergency">비상 비용</label>
                        <input type="number" id="emergency" class="asset-input" placeholder="금액">
                    </div>
                </div>
            </div>

            <div class="expense-section">
                <h3>저축/투자</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="savingsRatio">월급 대비 저축 비율 (%)</label>
                        <input type="number" id="savingsRatio" class="asset-input" placeholder="비율" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label for="investmentRatio">투자 비율 (%)</label>
                        <input type="number" id="investmentRatio" class="asset-input" placeholder="비율" min="0" max="100">
                    </div>
                </div>
                <div class="form-group">
                    <label for="investmentTypes">투자 상품 (복수 선택 가능)</label>
                    <div class="checkbox-group">
                        <input type="checkbox" id="stocks" name="investmentTypes" value="stocks">
                        <label for="stocks">주식</label>
                        <input type="checkbox" id="funds" name="investmentTypes" value="funds">
                        <label for="funds">펀드</label>
                        <input type="checkbox" id="realestate" name="investmentTypes" value="realestate">
                        <label for="realestate">부동산</label>
                        <input type="checkbox" id="crypto" name="investmentTypes" value="crypto">
                        <label for="crypto">암호화폐</label>
                    </div>
                </div>
            </div>

            <button onclick="analyzeAssets()" class="asset-button">분석하기</button>
        </div>
        <div id="chat-messages">
            <!-- 채팅 메시지들이 여기에 추가됩니다 -->
        </div>
        <div id="input-container">
            <input type="text" id="chatInput" placeholder="메시지를 입력하세요...">
            <button id="sendButton" onclick="sendMessage()">
                전송
                <svg class="send-icon" viewBox="0 0 24 24">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- 프롬프트 수정 모달 -->
    <div id="prompt-modal" class="modal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <h2>GPT 프롬프트 수정</h2>
            <textarea id="prompt-editor" placeholder="GPT 프롬프트를 입력하세요...">당신은 전문적인 자산관리 어시스턴트입니다. 사용자의 재무 상황을 분석하고, MBTI를 고려하여 맞춤형 조언을 제공합니다. 답변은 항상 친절하고 전문적이어야 하며, 실행 가능한 구체적인 조언을 포함해야 합니다.</textarea>
            <div style="text-align: right;">
                <button class="modal-button" onclick="resetPrompt()">초기화</button>
                <button class="modal-button" onclick="savePrompt()">저장</button>
            </div>
        </div>
    </div>

    <script>
        let isTyping = false;
        const userProfile = 'assets/user_profile.svg'; // 기본 사용자 프로필 이미지
        const mbtiProfileImages = {
            'ISTJ': 'assets/장미.jpg',
            'ISFJ': 'assets/장미.jpg',
            'INFJ': 'assets/장미.jpg',
            'INTJ': 'assets/징미.jpg',
            'ISTP': 'assets/세모.jpg',
            'ISFP': 'assets/세모.jpg',
            'INFP': 'assets/세모.jpg',
            'INTP': 'assets/세모.jpg',
            'ESTP': 'assets/코난.jpg',
            'ESFP': 'assets/코난.jpg',
            'ENFP': 'assets/코난.jpg',
            'ENTP': 'assets/코난.jpg',
            'ESTJ': 'assets/뭉치.jpg',
            'ESFJ': 'assets/뭉치.jpg',
            'ENFJ': 'assets/아름.jpg',
            'ENTJ': 'assets/아름.jpg'
        };

        async function appendMessage(sender, message) {
            const messagesDiv = document.getElementById('chat-messages');
            const messageContainer = document.createElement('div');
            messageContainer.className = `message-container ${sender === 'user' ? 'user-message' : 'bot-message'}`;
            
            // 프로필 이미지
            const profileImg = document.createElement('img');
            profileImg.className = 'chat-profile-img';
            
            if (sender === 'bot') {
                // 이미지 번호 설정 (객체에서 imageNumber 추출)
                const imageNumber = (typeof message === 'object' && message.imageNumber) ? message.imageNumber : 1;
                profileImg.src = `assets/profile${imageNumber}.jpg`;
            } else {
                // 사용자 MBTI에 따른 프로필 이미지 설정
                const mbti = document.getElementById('mbti')?.value?.toUpperCase();
                if (mbti && mbtiProfileImages[mbti]) {
                    profileImg.src = mbtiProfileImages[mbti];
                } else {
                    profileImg.src = userProfile;
                }
            }
            
            messageContainer.appendChild(profileImg);
            
            // 메시지 내용
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            // 메시지 텍스트
            const messageText = document.createElement('div');
            messageText.className = 'message-text';
            
            // 메시지 내용 처리
            let displayText = '';
            if (sender === 'user') {
                displayText = message;
            } else {
                try {
                    // 문자열이 아닌 경우 처리
                    if (typeof message === 'string') {
                        // 일반 텍스트 메시지인지 확인
                        try {
                            message = JSON.parse(message);
                        } catch (e) {
                            // JSON 파싱에 실패하면 일반 텍스트로 처리
                            displayText = message;
                            console.log('Using plain text message');
                        }
                    }
                    
                    if (typeof message === 'object') {
                        if (message.status === 'error') {
                            displayText = '오류가 발생했습니다: ' + message.error;
                        } else if (message.response) {
                            try {
                                const parsedResponse = JSON.parse(message.response);
                                displayText = parsedResponse.답변;
                            } catch (e) {
                                displayText = message.response;
                            }
                        } else {
                            displayText = message.답변 || '응답을 처리할 수 없습니다.';
                        }
                    }
                } catch (e) {
                    console.error('메시지 처리 오류:', e);
                    displayText = typeof message === 'string' ? message : '메시지 처리 중 오류가 발생했습니다.';
                }
            }
            
            messageText.textContent = displayText;
            messageContent.appendChild(messageText);
            
            // 시간 표시
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            const now = new Date();
            timeDiv.textContent = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
            messageContent.appendChild(timeDiv);
            
            messageContainer.appendChild(messageContent);
            messagesDiv.appendChild(messageContainer);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // 초기 웰컴 메시지
        window.addEventListener('load', function() {
            setTimeout(() => {
                appendMessage('bot', {
                    status: 'success',
                    response: JSON.stringify({
                        감정상태: '만족',
                        답변: '안녕하세요! 저는 AI 어시스턴트입니다. 어떤 도움이 필요하신가요?'
                    }),
                    imageNumber: 2
                });
            }, 500);
        });

        // 자산관리 폼 토글
        document.getElementById('toggle-asset-form').addEventListener('click', function() {
            const form = document.getElementById('asset-form');
            form.classList.toggle('hide');
        });

        async function analyzeAssets() {
            const data = {
                gender: document.querySelector('input[name="gender"]:checked')?.value,
                job: document.getElementById('job').value,
                incomeType: document.getElementById('incomeType').value,
                income: Number(document.getElementById('income').value),
                mbti: document.getElementById('mbti').value,
                fixedExpenses: {
                    housing: Number(document.getElementById('housing').value),
                    utilities: Number(document.getElementById('utilities').value),
                    insurance: Number(document.getElementById('insurance').value),
                    subscriptions: Number(document.getElementById('subscriptions').value)
                },
                variableExpenses: {
                    food: Number(document.getElementById('food').value),
                    transportation: Number(document.getElementById('transportation').value),
                    entertainment: Number(document.getElementById('entertainment').value),
                    emergency: Number(document.getElementById('emergency').value)
                },
                savings: {
                    savingsRatio: Number(document.getElementById('savingsRatio').value),
                    investmentRatio: Number(document.getElementById('investmentRatio').value),
                    investmentTypes: Array.from(document.querySelectorAll('input[name="investmentTypes"]:checked'))
                        .map(cb => cb.value)
                }
            };

            if (!validateData(data)) {
                alert('모든 필수 항목을 입력해주세요.');
                return;
            }

            isTyping = true;
            appendMessage('user', '자산 분석을 요청했습니다.');

            fetch('/process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'analyzeAssets',
                    data: data
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                appendMessage('bot', data);
                
                // 자산관리 창 닫기
                document.getElementById('asset-form').classList.add('hide');
                
                // 채팅 메시지 컨테이너 표시
                document.getElementById('chat-messages').style.display = 'block';
                
                // 채팅 입력창 표시
                document.getElementById('input-container').style.display = 'block';
            })
            .catch(error => {
                appendMessage('bot', '죄송합니다. 오류가 발생했습니다: ' + error.message);
            })
            .finally(() => {
                isTyping = false;
            });
        }

        function validateData(data) {
            if (!data.gender || !data.job || !data.income || !data.mbti) {
                return false;
            }
            return true;
        }

        function showTypingIndicator() {
            const chatbox = document.getElementById('chat-messages');
            const indicator = document.createElement('div');
            indicator.className = 'message bot typing-indicator';
            indicator.innerHTML = `
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            `;
            chatbox.appendChild(indicator);
            chatbox.scrollTop = chatbox.scrollHeight;
            return indicator;
        }

        async function sendMessage() {
            const input = document.getElementById('chatInput');
            const button = document.getElementById('sendButton');
            const message = input.value.trim();
            
            if (!message || isTyping) return;
            
            appendMessage('user', message);
            input.value = '';
            
            // 버튼 비활성화 및 로딩 상태
            button.disabled = true;
            isTyping = true;
            const typingIndicator = showTypingIndicator();

            try {
                const response = await fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });

                const data = await response.json();
                
                // 타이핑 효과 지연
                await new Promise(resolve => setTimeout(resolve, 500));
                
                if (data.error) {
                    appendMessage('bot', '오류: ' + data.error);
                } else {
                    appendMessage('bot', data.response);
                }
            } catch (error) {
                appendMessage('bot', '서버 연결 중 오류가 발생했습니다.');
            } finally {
                // 버튼 활성화 및 타이핑 상태 해제
                button.disabled = false;
                isTyping = false;
                typingIndicator.remove();
            }
        }

        // 엔터 키 이벤트
        document.getElementById('chatInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });

        // 프롬프트 수정 관련 스크립트
        const modal = document.getElementById('prompt-modal');
        const promptEditBtn = document.getElementById('prompt-edit-button');
        const closeBtn = document.querySelector('.modal-close');
        const defaultPrompt = "당신은 전문적인 자산관리 어시스턴트입니다. 사용자의 재무 상황을 분석하고, MBTI를 고려하여 맞춤형 조언을 제공합니다. 답변은 항상 친절하고 전문적이어야 하며, 실행 가능한 구체적인 조언을 포함해야 합니다.";

        promptEditBtn.onclick = function() {
            modal.style.display = "flex";
        }

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function resetPrompt() {
            document.getElementById('prompt-editor').value = defaultPrompt;
        }

        function savePrompt() {
            const newPrompt = document.getElementById('prompt-editor').value;
            // 프롬프트를 서버에 저장하는 API 호출
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'updatePrompt',
                    prompt: newPrompt
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                alert('프롬프트가 성공적으로 저장되었습니다.');
                modal.style.display = "none";
            })
            .catch(error => {
                alert('프롬프트 저장 중 오류가 발생했습니다: ' + error.message);
            });
        }

        // 재무 목표 추가 기능
        document.querySelectorAll('.add-goal-btn').forEach(button => {
            button.addEventListener('click', function() {
                const type = this.dataset.type;
                const goalsContainer = this.previousElementSibling;
                const goalItems = goalsContainer.querySelectorAll('.goal-item');
                const newIndex = goalItems.length + 1;
                
                const newGoalItem = document.createElement('div');
                newGoalItem.className = 'goal-item';
                
                newGoalItem.innerHTML = `
                    <input type="text" id="${type}_term_goal_${newIndex}" 
                           name="${type}_term_goal_${newIndex}" 
                           placeholder="목표 입력">
                    <input type="number" id="${type}_term_amount_${newIndex}" 
                           name="${type}_term_amount_${newIndex}" 
                           placeholder="목표 금액">
                    <input type="date" id="${type}_term_date_${newIndex}" 
                           name="${type}_term_date_${newIndex}">
                `;
                
                goalsContainer.appendChild(newGoalItem);
            });
        });

        // 페이지 로드 시 저장된 데이터 불러오기
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('process.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'loadUserData'
                    })
                });
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    // 폼 필드에 데이터 채우기
                    populateFormData(result.data);
                }
            } catch (error) {
                console.error('데이터 로드 중 오류:', error);
            }
        });

        // 폼 데이터 채우기 함수
        function populateFormData(data) {
            // 기본 입력 필드 채우기
            for (const [key, value] of Object.entries(data)) {
                const element = document.getElementById(key);
                if (element) {
                    if (element.type === 'radio') {
                        const radio = document.querySelector(`input[name="${key}"][value="${value}"]`);
                        if (radio) radio.checked = true;
                    } else if (element.type === 'checkbox') {
                        element.checked = value === 'true' || value === true;
                    } else {
                        element.value = value;
                    }
                }
            }

            // 수입 정보 채우기
            if (data.salary_before_tax) document.getElementById('salary_before_tax').value = data.salary_before_tax;
            if (data.salary_after_tax) document.getElementById('salary_after_tax').value = data.salary_after_tax;
            if (data.income_type) document.getElementById('income_type').value = data.income_type;
            if (data.rental_income) document.getElementById('rental_income').value = data.rental_income;
            if (data.investment_income) document.getElementById('investment_income').value = data.investment_income;
            if (data.freelance_income) document.getElementById('freelance_income').value = data.freelance_income;

            // 재무 목표 채우기
            if (data.goals) {
                data.goals.forEach((goal, index) => {
                    if (index > 0) {
                        // 필요한 경우 새 목표 필드 추가
                        const addButton = document.querySelector(`.add-goal-btn[data-type="${goal.type}"]`);
                        if (addButton) addButton.click();
                    }
                    
                    const typePrefix = goal.type + '_term';
                    document.getElementById(`${typePrefix}_goal_${index + 1}`).value = goal.description;
                    document.getElementById(`${typePrefix}_amount_${index + 1}`).value = goal.amount;
                    document.getElementById(`${typePrefix}_date_${index + 1}`).value = goal.date;
                });
            }
        }
    </script>
</body>
</html>
