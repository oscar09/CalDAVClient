<?php
/**
 * Class CalDAVClientGoogleTest.
 * VALID tests.
 */

class CalDAVClientGoogleTest extends PHPUnit_Framework_TestCase {
	protected $client;

	//CHANGE THIS !!
	private $user = 'username';
	private $password = 'password';
	private $username, $calendarUrl, $relativeUrl;


	protected function setUp()
	{
		$this->username = "$this->user@gmail.com";
		$this->calendarUrl = "https://www.google.com/calendar/dav/$this->user@gmail.com/events/";
		$this->relativeUrl = "/calendar/dav/$this->user@gmail.com/events";

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
	 * Tests REPORT method.
	 */
	public function testReportAbsolute()
	{
		$body = '<c:calendar-query xmlns:d="DAV:" xmlns:c="urn:ietf:params:xml:ns:caldav"><d:prop><d:getetag /><c:calendar-data /></d:prop><c:filter><c:comp-filter name="VCALENDAR" /></c:filter></c:calendar-query>';
		$result = $this->client->doReport(null, $body);
		$this->assertEquals('207', $result->getResponseCode());
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
