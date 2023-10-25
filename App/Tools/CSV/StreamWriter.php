<?php

namespace App\Tools\CSV;

/**
 * Since the \CSV\Writer works perfectly for streams, just make a new type since we can't make instantiate an abstract class
 *
 * User is responsible for opening and closing the stream.
 */
class StreamWriter extends Writer
	{
	}
