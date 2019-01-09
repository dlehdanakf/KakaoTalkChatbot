<?php
	class IndexController {
		public function skillViewApplicationIndex(){
			$temporary_thumbnail = "http://kung.kr/files/attach/images/247/123/041/006/bca9d3b106a8d89f73a9fc40daef22b2.png";
			$temporary_alt = "temporary thumbnail";

			$skillResponse = new SkillResponse;
			$carousel = new Carousel;

			/** 0. 환영문구 추가 */
			/**
			 *
			 */

			/** 1. 캐로셀에 카드 추가 */
			/** 1.0 학식 알리미 */
			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail, $temporary_alt)));
			$basicCard->addButton((new Button("오늘의 학식메뉴"))->setMessageText("오늘의 학식메뉴 알려줘"));
			$basicCard->addButton((new Button("학식 이용후기 남기기"))->setMessageText("학식 이용후기 남길래"));
			$basicCard->addButton((new Button("단무지의 추천 ⭐"))->setMessageText("단무지야 혼밥장소 추천해줘"));
			$carousel->addCard($basicCard);

			/** 1.1 제휴업체 목록 */
			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail, $temporary_alt)));
			$basicCard->addButton((new Button("배달음식 주문하기"))->setMessageText("배달음식점 목록 보여줘"));
			$basicCard->addButton((new Button("일반음식점, 카페, 술집"))->setMessageText("학교주변 맛집 알려줘"));
			$basicCard->addButton((new Button("문화시설, 헬스, 기타"))->setMessageText("기타 제휴업체 알려줘"));
			$carousel->addCard($basicCard);

			/** 1.2 문화초대 이벤트 */
			$link = new Link("http://facebook.com/kunnectTimetable");
			$link->pc = "http://facebook.com/kunnectTimetable";
			$link->mobile = "fb://page/1967018396922579";

			$basicCard = new BasicCard;
			$basicCard->title = "건국대학교 학우에게만 제공되는 다양한 무료 이벤트!";
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail, $temporary_alt)));
			$basicCard->addButton((new Button("최신 문화초대 이벤트"))->setMessageText("최신 문화초대 이벤트 알려줘"));
			$basicCard->addButton((new Button("쿠넥트 페이스북 페이지"))->setOsLink($link));
			$carousel->addCard($basicCard);

			/** 1.3 학사일정 */
			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail, $temporary_alt)));
			$basicCard->addButton((new Button("종강(개강)일 계산기"))->setMessageText("종강(개강)까지 얼마나 남았어?"));
			$basicCard->addButton((new Button("이번학기 학사일정"))->setMessageText("이번학기 학사일정 알려줘"));
			$basicCard->addButton((new Button("교내 전화번호부 검색"))->setMessageText("우리학교 단과대학목록 보여줘"));
			$carousel->addCard($basicCard);

			/** 1.4 건대신문 */
			$basicCard = new BasicCard;
			$basicCard->setThumbnail((new Thumbnail($temporary_thumbnail, $temporary_alt)));
			$basicCard->addButton((new Button("건대신문 최신기사"))->setMessageText("건대신문 최신기사 보여줘"));
			$basicCard->addButton((new Button("기사 제보(투고)하기"))->setWebLinkUrl("http://popkon.konkuk.ac.kr/com/jb.html"));
			$basicCard->addButton((new Button("공식 페이스북 페이지"))->setWebLinkUrl("http://facebook.com/kkpressb"));
			$carousel->addCard($basicCard);

			$skillResponse->addResponseComponent($carousel);
			return json_encode($skillResponse->render());
		}
	}