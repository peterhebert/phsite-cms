<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * NEW Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Pagination
 * @author		Peter Hebert
 * @link		
 */
class MY_Pagination extends CI_Pagination {

	var $first_text   		= 'first';
	var $prev_text			= 'previous';
	var $next_text			= 'next';
	var $last_text			= 'last';

	function MY_Pagination($params = array())
	{
		parent::CI_Pagination();

		$this->first_link = '&laquo;';
		$this->next_link = '&gt;';
		$this->prev_link = '&lt;';
		$this->last_link = '&raquo;';

		//echo "custom pagination class";
	}



	function create_links_proper()
	{
		$CI =& get_instance();

		// If our item count or per-page total is zero there is no need to continue.
		if ($this->total_rows == 0 OR $this->per_page == 0)
		{
			return '';
		}

		// Calculate the total number of pages
		$num_pages = ceil($this->total_rows / $this->per_page);

		// Is there only one page? Hm... nothing more to do here then.
		if ($num_pages == 1)
		{
			return '';
		}

		$this->num_links = (int)$this->num_links;
		
		$total_links = ($this->num_links * 2) + 1;

		if ($this->num_links < 1)
		{
			//show_error('Your number of links must be a positive number.');
		}

		if (!is_numeric($this->cur_page) || $this->cur_page == '')
		{
			$this->cur_page = 1;
		}

		// Is the page number beyond the last page?
		// If so we show the last page
		if ($this->cur_page > $num_pages)
		{
			$this->cur_page = num_pages;
		}

		$uri_page_number = $this->cur_page;

		// Calculate the start and end numbers. These determine
		// which number to start and end the digit links with
		$start = $this->cur_page - $this->num_links;
		if($start <= 0)
		{
			$start = 1;
		}
		
		$end = $start + $total_links;
		if($end > $num_pages)
		{
			$end = $num_pages;
		}

		// Is pagination being used over GET or POST?  If get, add a per_page query
		// string. If post, add a trailing slash to the base URL if needed
		if ($CI->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE)
		{
			$this->base_url = rtrim($this->base_url).'&amp;'.$this->query_string_segment.'=';
		}
		else
		{
			$this->base_url = rtrim($this->base_url, '/') .'/';
		}

  		// And here we go...
		$output = '';

		// Render the "First" link
		if  ($this->cur_page > ($this->num_links + 1)  && $num_pages > $total_links)
		{
			$txt = trim($this->first_link.' '.$this->first_text);
			$output .= $this->first_tag_open.'<a href="'.$this->base_url.'">'.$txt.'</a>'.$this->first_tag_close;
		}

		// Render the "previous" link
		if  ($this->cur_page > 1  && $num_pages > $total_links)
		{
			$i = $this->cur_page - 1;
			$txt = trim($this->prev_link.' '.$this->prev_text);
			$output .= $this->prev_tag_open.'<a href="'.$this->base_url.$i.'">'.$txt.'</a>'.$this->prev_tag_close;
		}

		// Write the digit links
		for ($loop = $start; $loop <= $end; $loop++)
		{
			$i = $loop;

			if ($i >= 0)
			{
				if ($this->cur_page == $loop)
				{
					$output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
				}
				else
				{
					$n = ($i == 0) ? '' : $i;
					$output .= $this->num_tag_open.'<a href="'.$this->base_url.$n.'">'.$loop.'</a>'.$this->num_tag_close;
				}
			}
		}

		// Render the "next" link
		if ($this->cur_page < $num_pages && $num_pages > $total_links)
		{
			$i = $this->cur_page + 1;
			$txt = trim($this->next_text.' '.$this->next_link);
			$output .= $this->next_tag_open.'<a href="'.$this->base_url.$i.'">'.$txt.'</a>'.$this->next_tag_close;
		}

		// Render the "Last" link
		if (($this->cur_page + $this->num_links) < $num_pages && $num_pages > $total_links)
		{
			$txt = trim($this->last_text.' '.$this->last_link);
			$output .= $this->last_tag_open.'<a href="'.$this->base_url.$num_pages.'">'.$txt.'</a>'.$this->last_tag_close;
		}

		// Kill double slashes.  Note: Sometimes we can end up with a double slash
		// in the penultimate link so we'll kill all double slashes.
		$output = preg_replace("#([^:])//+#", "\\1/", $output);

		// Add the wrapper HTML if exists
		$output = $this->full_tag_open.$output.$this->full_tag_close;

		return $output;
	}

}
// END Pagination Class

/* End of file Pagination.php */
/* Location: ./system/libraries/Pagination.php */