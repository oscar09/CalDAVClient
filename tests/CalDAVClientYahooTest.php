<?php
/**
 * Class CalDAVClientYahooTest.
 * VALID tests.
 */

class CalDAVClientYahooTest extends PHPUnit_Framework_TestCase {
	protected $client;

	//CHANGE THIS !!
	private $username = 'USERID@yahoo.com';
	private $password = 'password';
	private $calendarUrl = 'https://caldav.calendar.yahoo.com/dav/userid%40yahoo.com/Calendar/USERID';
	private $relativeUrl = '/dav/USERID%40yahoo.com/Calendar/USERID';

	protected function setUp()
	{
		$this->client = new CalDAVClient($this->calendarUrl, $this->username, $this->password);
	}

	/**
	 * Tests OPTIONS method.
	 */
	public function testOptionsAbsolute()
	{
		$result = $this->client->doOptions();

		$this->assertEquals('200', $result->getResponseCode());

		$headers = $result->getHeaders();
		$this->assertFalse(empty($headers));
	}

	/**
	 * Tests PROPFIND method.
	 */
	public function testPropFindAbsolute()
	{
		$result = $this->client->doPropFind();
		$this->assertEquals('207', $result->getResponseCode());

		$body_has_multistatus = strlen($result->getBody());
		$this->assertGreaterThan(1, $body_has_multistatus);
	}

	/**
	 * Tests PUT and DELETE method.
	 */
	public function testPutAndDelete()
	{

		$randomUID = uniqid();
		$iCalString =
"BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//flypig.co.uk/iCalGen 0.1//EN
BEGIN:VEVENT
UID:".$randomUID."\n".
"DTSTAMP:20130424T181649Z
DTSTART:20130426T130000Z
DTEND:20130426T140000Z
SUMMARY:phpunit
DESCRIPTION:phpunit
LOCATION:office
END:VEVENT
END:VCALENDAR";

		//PUT
		$result = $this->client->doPut($this->relativeUrl.'/'.$randomUID.'.ics', $iCalString);
		$this->assertEquals('201', $result->getResponseCode());

		//DELETE
		$result = $this->client->doDelete($this->relativeUrl.'/'.$randomUID.'.ics');
		$this->assertEquals($result->getResponseCode(), '204');
	}

	/**
	 * Tests OPTION method on a relative path.
	 */
	public function testOptionsRelative()
	{
		$result = $this->client->doOptions($this->relativeUrl);

		$this->assertEquals('200', $result->getResponseCode());

		$headers = $result->getHeaders();
		$this->assertFalse(empty($headers));
	}

	/**
	 * Tests PROPFIND method on a relative path.
	 */
	public function testPropFindRelative()
	{
		$result = $this->client->doPropFind($this->relativeUrl);

		$this->assertEquals('207', $result->getResponseCode());

		$body_has_multistatus = strlen($result->getBody());
		$this->assertGreaterThan(1, $body_has_multistatus);
	}
}
