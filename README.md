# PHP Project

## 프로젝트 소개
이 프로젝트는 PHP로 개발된 웹 애플리케이션입니다.

## 시작하기

### 필수 요구사항
- PHP 7.4 이상
- MySQL 5.7 이상
- Composer

### 설치 방법

1. 저장소를 클론합니다:
```bash
git clone [repository-url]
cd [project-directory]
```

2. Composer 의존성을 설치합니다:
```bash
composer install
```

3. 환경 설정:
   - `.env.example` 파일을 복사하여 `.env` 파일을 생성합니다
   - `.env` 파일에서 데이터베이스 설정 등을 환경에 맞게 수정합니다
```bash
cp .env.example .env
```

4. 애플리케이션 실행:
```bash
php -S localhost:8000
```

## 주요 기능
- 사용자 데이터 처리
- 로그 기록
- 기타 기능들...

## 프로젝트 구조
```
├── index.php          # 메인 진입점
├── process.php        # 데이터 처리
├── .env              # 환경 설정 (git에서 제외됨)
├── .env.example      # 환경 설정 예시
└── .gitignore        # git 제외 파일 목록
```

## 실행 결과 스크린샷

### 프로필 입력 폼
![프로필 입력 폼](KakaoTalk_Photo_2024-12-01-23-45-04-1.png)

### AI 상담 채팅
![채팅1](KakaoTalk_Photo_2024-12-01-23-45-04-2.png)
![채팅2](KakaoTalk_Photo_2024-12-01-23-45-04-3.png)

### 지출 입력 폼
![지출 입력](KakaoTalk_Photo_2024-12-01-23-45-04-4.png)

### AI 분석 결과
![분석 결과](KakaoTalk_Photo_2024-12-01-23-45-04-5.png)

## 라이선스
This project is licensed under the MIT License - see the LICENSE file for details
