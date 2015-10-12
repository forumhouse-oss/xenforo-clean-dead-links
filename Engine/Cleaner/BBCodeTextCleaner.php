<?php

/**
 * Class, that performs cleaning of BBCode-encoded links in the text
 */
class FH_LinkCleaner_Engine_Cleaner_BBCodeTextCleaner extends FH_LinkCleaner_Engine_Cleaner_Abstract
{
    /**
     * RegEx to find and parse links of the following format:
     *   - [url="http://example.com"]Link text[/url]
     *   - [url=http://example.com]Link text[/url]
     *   - [url]http://www.example.com[/url]
     */
    const BB_CODE_URL_REGEX = '#\[url(?:=["\']?(.+?)?)?["\']?\](.+?)\[/url\]#ismu';

    /**
     * RegEx to parse [IMG]http://www.example.com/image.jpg[/IMG] bbcodes
     */
    const BB_CODE_IMG_REGEX = '#\[img\](.+?)\[/img\]#ismu';

    /**
     * RegEx to remove simple empty [img] or [url] bbcodes
     */
    const BB_CODE_EMPTY_TAGS_SIMPLE = '#\r?\n?\[(?:img|url)\]\s*\[/(?:img|url)\]\r?\n?#ismu';

    /**
     * RegEx to remove empty tags with an option like [quote="hsgdhsdhsgd"][/quote]
     */
    const BB_CODE_EMPTY_TAGS_WITH_OPTION = '#\r?\n?\[(?:quote)=[^\]]+\]\s*\[/(?:quote)\]\r?\n?#ismu';

    /**
     * @param string $content
     *
     * @return string Cleaned content
     */
    public function clean($content)
    {
        $content = preg_replace_callback(self::BB_CODE_URL_REGEX, array($this, 'cleanUrlTagContents'), $content);
        $this->assertIsNotRegExError($content, 'BB_CODE_URL_REGEX');

        $content = preg_replace_callback(self::BB_CODE_IMG_REGEX, array($this, 'cleanImgTagContents'), $content);
        $this->assertIsNotRegExError($content, 'BB_CODE_IMG_REGEX');

        $content = preg_replace(self::BB_CODE_EMPTY_TAGS_SIMPLE, ' ', $content);
        $this->assertIsNotRegExError($content, 'BB_CODE_EMPTY_TAGS_SIMPLE');

        $content = preg_replace(self::BB_CODE_EMPTY_TAGS_WITH_OPTION, ' ', $content);
        $this->assertIsNotRegExError($content, 'BB_CODE_EMPTY_TAGS_WITH_OPTION');

        return $content;
    }

    /**
     * Method to match and clean [url] enclosed links
     *
     * @param array $matches Array. [0] = full bbcode, [1] = url (if found), [2] = body
     *
     * @return string
     */
    private function cleanUrlTagContents(array $matches)
    {
        $url = !empty($matches[1]) ? $matches[1] : $matches[2];
        $body = $matches[2];

        if (!in_array($url, $this->links)) {
            return $matches[0];
        }

        if (($url === $body) || in_array($body, $this->links)) {
            return '';
        }

        return $body;
    }

    /**
     * Method to match and clean [img] enclosed links
     *
     * @param array $matches [0] = full bbcode, [1] = body
     *
     * @return string
     */
    private function cleanImgTagContents(array $matches)
    {
        $url = $matches[1];

        if (!in_array($url, $this->links)) {
            return $matches[0];
        }

        return '';
    }

    /**
     * @param string $content
     * @param string $operationDescription
     *
     * @throws Exception
     */
    private function assertIsNotRegExError($content, $operationDescription)
    {
        if (null === $content) {
            throw new Exception("Regular expression operation error at operation $operationDescription");
        }
    }
}
