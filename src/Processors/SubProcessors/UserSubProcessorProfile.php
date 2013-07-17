<?php
class UserSubProcessorProfile extends UserSubProcessor
{
	const URL_PROFILE = 0;

	public function getURLs($userName)
	{
		return
		[
			self::URL_PROFILE => 'http://myanimelist.net/profile/' . $userName,
		];
	}

	public function process(array $documents, &$context)
	{
		$doc = self::getDOM($documents[self::URL_PROFILE]);
		$xpath = new DOMXPath($doc);

		$userName       = Strings::removeSpaces(self::getNodeValue($xpath, '//title'));
		$userName       = substr($userName, 0, strpos($userName, '\'s Profile'));
		$pictureUrl     = self::getNodeValue($xpath, '//td[@class = \'profile_leftcell\']//img', null, 'src');
		$joinDate       = Strings::makeDate(self::getNodeValue($xpath, '//td[text() = \'Join Date\']/following-sibling::td'));
		$malId          = Strings::makeInteger(Strings::parseURL(self::getNodeValue($xpath, '//a[text() = \'All Comments\']', null, 'href'))['query']['id']);
		$animeViewCount = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Anime List Views\']/following-sibling::td'));
		$mangaViewCount = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Manga List Views\']/following-sibling::td'));
		$commentCount   = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Comments\']/following-sibling::td'));
		$postCount      = Strings::makeInteger(self::getNodeValue($xpath, '//td[text() = \'Forum Posts\']/following-sibling::td'));
		$birthday       = Strings::makeDate(self::getNodeValue($xpath, '//td[text() = \'Birthday\']/following-sibling::td'));
		$location       = Strings::removespaces(self::getNodeValue($xpath, '//td[text() = \'Location\']/following-sibling::td'));
		$website        = Strings::removeSpaces(self::getNodeValue($xpath, '//td[text() = \'Website\']/following-sibling::td'));
		$gender         = Strings::makeEnum(self::getNodeValue($xpath, '//td[text() = \'Gender\']/following-sibling::td'), ['Female' => UserGender::Female, 'Male' => UserGender::Male], UserGender::Unknown);

		$this->delete('users', ['LOWER(name)' => strtolower($userName)]);
		$userId = $this->insert('users', [
			'name' => $userName,
			'picture_url' => $pictureUrl,
			'join_date' => $joinDate,
			'mal_id' => $malId,
			'comments' => $commentCount,
			'posts' => $postCount,
			'birthday' => $birthday,
			'location' => $location,
			'website' => $website,
			'gender' => $gender,
			'anime_views' => $animeViewCount,
			'manga_views' => $mangaViewCount,
			'processed' => date('Y-m-d H:i:s'),
		]);
		$context->userId = $userId;
	}
}
