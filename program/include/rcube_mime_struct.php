
    function getStructurePartType($structure, $part)
    {
	    $part_a = self::getPartArray($structure, $part);
	    if (!empty($part_a)) {
		    if (is_array($part_a[0]))
                return 'multipart';
		    else if ($part_a[0])
                return $part_a[0];
	    }

        return 'other';
    }

    function getStructurePartEncoding($structure, $part)
    {
	    $part_a = self::getPartArray($structure, $part);
	    if ($part_a) {
		    if (!is_array($part_a[0]))
                return $part_a[5];
	    }

        return '';
    }

    function getStructurePartCharset($structure, $part)
    {
	    $part_a = self::getPartArray($structure, $part);
	    if ($part_a) {
		    if (is_array($part_a[0]))
                return '';
		    else {
			    if (is_array($part_a[2])) {
				    $name = '';
				    while (list($key, $val) = each($part_a[2]))
                        if (strcasecmp($val, 'charset') == 0)
                            return $part_a[2][$key+1];
			    }
		    }
	    }

        return '';
    }

    function getStructurePartArray($a, $part)
    {
	    if (!is_array($a)) {
            return false;
        }
	    if (strpos($part, '.') > 0) {
		    $original_part = $part;
		    $pos = strpos($part, '.');
		    $rest = substr($original_part, $pos+1);
		    $part = substr($original_part, 0, $pos);
		    if ((strcasecmp($a[0], 'message') == 0) && (strcasecmp($a[1], 'rfc822') == 0)) {
			    $a = $a[8];
		    }
		    return self::getPartArray($a[$part-1], $rest);
	    }
        else if ($part>0) {
		    if (!is_array($a[0]) && (strcasecmp($a[0], 'message') == 0)
                && (strcasecmp($a[1], 'rfc822') == 0)) {
			    $a = $a[8];
		    }
		    if (is_array($a[$part-1]))
                return $a[$part-1];
		    else
                return $a;
	    }
        else if (($part == 0) || (empty($part))) {
		    return $a;
	    }
    }
