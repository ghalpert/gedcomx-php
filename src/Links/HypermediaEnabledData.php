<?php
/**
 *
 * 
 *
 * Generated by <a href="http://enunciate.codehaus.org">Enunciate</a>.
 *
 */

namespace Gedcomx\Links;

use Gedcomx\Common\ExtensibleData;

/**
 * An data type that supports hypermedia controls (i.e. links).
 */
class HypermediaEnabledData extends ExtensibleData implements SupportsLinks
{

    /**
     * The list of hypermedia links. Links are not specified by GEDCOM X core, but as extension elements by GEDCOM X RS.
     *
     * @var Link[]
     */
    private $links;

    /**
     * Constructs a HypermediaEnabledData from a (parsed) JSON hash
     *
     * @param mixed $o Either an array (JSON) or an XMLReader.
     *
     * @throws \Exception
     */
    public function __construct($o = null)
    {
        if (is_array($o)) {
            $this->initFromArray($o);
        }
        else if ($o instanceof \XMLReader) {
            $success = true;
            while ($success && $o->nodeType != \XMLReader::ELEMENT) {
                $success = $o->read();
            }
            if ($o->nodeType != \XMLReader::ELEMENT) {
                throw new \Exception("Unable to read XML: no start element found.");
            }

            $this->initFromReader($o);
        }
    }

    /**
     * The list of hypermedia links. Links are not specified by GEDCOM X core, but as extension elements by GEDCOM X RS.
     *
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * The list of hypermedia links. Links are not specified by GEDCOM X core, but as extension elements by GEDCOM X RS.
     *
     * @param Link[] $links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }
    /**
     * Returns the associative array for this HypermediaEnabledData
     *
     * @return array
     */
    public function toArray()
    {
        $a = parent::toArray();
        if ($this->links) {
            $ab = array();
            foreach ($this->links as $i => $x) {
                $ab[$i] = $x->toArray();
            }
            $a['links'] = $ab;
        }
        return $a;
    }


    /**
     * Initializes this HypermediaEnabledData from an associative array
     *
     * @param array $o
     */
    public function initFromArray(array $o)
    {
        $this->links = array();
        if (isset($o['links'])) {
            foreach ($o['links'] as $i => $x) {
                if( ! array_key_exists("rel", $x) ){
                    $x["rel"] = $i;
                }
                $this->links[$i] = new Link($x);
            }
            unset($o['links']);
        }
        parent::initFromArray($o);
    }

    /**
     * Sets a known child element of HypermediaEnabledData from an XML reader.
     *
     * @param \XMLReader $xml The reader.
     * @return bool Whether a child element was set.
     */
    protected function setKnownChildElement(\XMLReader $xml) {
        $happened = parent::setKnownChildElement($xml);
        if ($happened) {
          return true;
        }
        else if (($xml->localName == 'link') && ($xml->namespaceURI == 'http://gedcomx.org/v1/')) {
            $child = new Link($xml);
            if (!isset($this->links)) {
                $this->links = array();
            }
            array_push($this->links, $child);
            $happened = true;
        }
        return $happened;
    }

    /**
     * Sets a known attribute of HypermediaEnabledData from an XML reader.
     *
     * @param \XMLReader $xml The reader.
     * @return bool Whether an attribute was set.
     */
    protected function setKnownAttribute(\XMLReader $xml) {
        if (parent::setKnownAttribute($xml)) {
            return true;
        }

        return false;
    }

    /**
     * Writes the contents of this HypermediaEnabledData to an XML writer. The startElement is expected to be already provided.
     *
     * @param \XMLWriter $writer The XML writer.
     */
    public function writeXmlContents(\XMLWriter $writer)
    {
        parent::writeXmlContents($writer);
        if ($this->links) {
            foreach ($this->links as $i => $x) {
                $writer->startElementNs('gx', 'link', null);
                $x->writeXmlContents($writer);
                $writer->endElement();
            }
        }
    }

    /**
     * @param \Gedcomx\Links\Link $link
     */
    public function addLink(Link $link)
    {
        $this->links[] =  $link;
    }

    /**
     * Add a hypermedia link relationship
     *
     * @param string $rel  see Gedcom\Rs\Client\Rel
     * @param string $href The target URI.
     */
    public function addLinkRelation($rel, $href)
    {
        $this->links[] = new Link( array(
            "rel" => $rel,
            "href" => $href
        ));
    }

    /**
     * Add a templated link.
     *
     * @param string $rel      see Gedcom\Rs\Client\Rel
     * @param string $template The link template.
     */
    public function addTemplatedLink($rel, $template)
    {
        $this->links[] = new Link( array(
            "rel" => $rel,
            "template" => $template
        ));
    }

    /**
     * Get a link by its rel.
     *
     * @param string $rel see Gedcom\Rs\Client\Rel
     *
     * @return Link
     */
    public function getLink($rel)
    {
        if( $this->links != null ){
            foreach ( $this->links as $idx => $link ) {
                if ( $link->getRel() == $rel ) {
                    return $link;
                }
            }
        }
        return null;
    }

    /**
     * Get a list of links by rel.
     *
     * @param string $rel see Gedcom\Rs\Client\Rel
     *
     * @return Link[]
     */
    public function getLinksByRel($rel)
    {
        $links = array();
        foreach ( $this->links as $link ) {
            if ($link->getRel() == $rel) {
                $links[] = $rel;
            }
        }
        return $links;
    }
}