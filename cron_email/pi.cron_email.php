<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
Copyright (C) 2005 - 2011 EllisLab, Inc.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
ELLISLAB, INC. BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of EllisLab, Inc. shall not be
used in advertising or otherwise to promote the sale, use or other dealings
in this Software without prior written authorization from EllisLab, Inc.
*/

$plugin_info = array(
						'pi_name'			=> 'Send Email',
						'pi_version'		=> '1.1',
						'pi_author'			=> 'Paul Burdick',
						'pi_author_url'		=> 'http://www.expressionengine.com/',
						'pi_description'	=> 'Cron based email sending',
						'pi_usage'			=> Cron_email::usage()
					);

/**
 * Cron_email Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			ExpressionEngine Dev Team
 * @copyright		Copyright (c) 2005 - 2011, EllisLab, Inc.
 * @link			http://expressionengine.com/downloads/details/cron_send_email/
 */
class Cron_email {

    var $return_data = '';
    
	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
    function Cron_email()
    {
        $this->EE =& get_instance();

        $to		 = ($this->EE->TMPL->fetch_param('to') !== FALSE)   ? $this->EE->TMPL->fetch_param('to')   : '';
        $cc		 = ($this->EE->TMPL->fetch_param('cc') !== FALSE)   ? $this->EE->TMPL->fetch_param('cc')   : '';
        $bcc	 = ($this->EE->TMPL->fetch_param('bcc') !== FALSE)  ? $this->EE->TMPL->fetch_param('bcc')  : '';
        $from	 = ($this->EE->TMPL->fetch_param('from') !== FALSE) ? $this->EE->TMPL->fetch_param('from') : $this->EE->config->item('webmaster_email');
        $subject = ($this->EE->TMPL->fetch_param('subject') !== FALSE) ? $this->EE->TMPL->fetch_param('subject') : '';
        $message = $this->EE->TMPL->tagdata;
        
        if ($to == '' OR $subject == '' OR $message == '') return false;
        
        if ($this->EE->TMPL->fetch_param('parse_tag') == 'on' && stristr($message, '{'))
        {
        	$top	= '';
        	$bottom	= '';
        	
        	if (preg_match("/".LD.'email_top'.RD."(.*?)".LD.SLASH.'email_top'.RD."/s", $this->EE->TMPL->tagdata, $matches))
        	{
        		$top = $matches['1'];
        		$this->EE->TMPL->tagdata = str_replace($matches['0'], '', $this->EE->TMPL->tagdata);
        	}
        	
        	if (preg_match("/".LD.'email_bottom'.RD."(.*?)".LD.SLASH.'email_bottom'.RD."/s", $this->EE->TMPL->tagdata, $matches))
        	{
        		$bottom = $matches['1'];
        		$this->EE->TMPL->tagdata = str_replace($matches['0'], '', $this->EE->TMPL->tagdata);
        	}
        
        	// ----------------------------------------
        	//  Fetch the channel entry
        	// ----------------------------------------
		
			if ( ! class_exists('Channel'))
        	{
        		require APPPATH.'modules/channel/mod.channel'.EXT;
        	}

        	$channel = new Channel;        
        	
        	$channel->fetch_custom_channel_fields();
        	$channel->fetch_custom_member_fields();
        	$channel->build_sql_query();
        	$channel->query = $this->EE->db->query($channel->sql);
        	
        	if ($channel->query->num_rows() == 0)
        	{
        	    return false;
        	}     
        
			$this->EE->load->library('typography');
        
        	$this->EE->typography->encode_email = false;
        	
        	$this->EE->TMPL->tagparams['rdf'] = 'off'; // Turn off RDF code
        	
        	$channel->fetch_categories();
        	$channel->parse_channel_entries();
        	$message = $top.$channel->return_data.$bottom;
        }
        
		$this->EE->load->helper('text');
        $message = entities_to_ascii($message);
        
		$this->EE->load->library('email');
		
		$this->EE->email->wordwrap = FALSE;
        $this->EE->email->EE_initialize();
        $this->EE->email->to($to);
        $this->EE->email->cc($cc);
        $this->EE->email->bcc($bcc);
        $this->EE->email->from($from);
        $this->EE->email->subject($subject);
       	$this->EE->email->message($message);
		$this->EE->email->send();

		return TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Usage
	 *
	 * Plugin Usage
	 *
	 * @access	public
	 * @return	string
	 */
	function usage()
	{
		ob_start(); 
		?>

		Allows you to schedule the sending of an email to the email addresses specified 
		in the parameters.  The tag data (that which is between the opening and closing tag) 
		will be the contents of the email's message.

		As a perk, you can have the tag data parsed exactly as if it were part of 
		an {exp:channel:entries} tag.  This allows you to, say, schedule the sending of an 
		email at the beginning of every day containing the most recently posted entries of that day.


		=====================
		Parameters
		=====================

		to="" 		 - Recipient(s) of email [required]

		from=""		 - Sender of email [optional, default webmaster of site]

		cc=""		 - CC Recipient(s) of email [required]

		bcc=""		 - BCC Recipient(s) of email [required]

		subject=""	 - Subject line of email [required]

		parse_tag="" - If set to 'on' it will parse the tagdata as if it were part of a 
		{exp:channel:entries} tag.  When set to 'on' the tag will accept all of the usual
		parameters for the {exp:channel:entries} tag as well. [optional]

		=====================
		Pair Variables
		=====================

		{email_top}{/email_top} - When parse_tag is set to "on" the content between this variable pair will be removed from
		the tagdata (i.e. not parsed) and placed at the top of the sent email.  Think email heading and opening statement

		{email_bottom}{/email_bottom} - When parse_tag is set to "on" the content between this variable pair will be removed from
		the tagdata (i.e. not parsed) and placed at the bottom of the sent email. Think signature.

		=====================
		EXAMPLES
		=====================

		{exp:cron plugin="cron_email" day="23" minute="59" to="webmaster@mysite.com" subject="Daily Email"}

		Hello There!

		{/exp:cron}


		Version 1.1
		******************
		- Updated plugin to be 2.0 compatible

		<?php
		
		$buffer = ob_get_contents();
	
		ob_end_clean(); 

		return $buffer;
	}

	// --------------------------------------------------------------------
	
}
// END CLASS

/* End of file pi.cron_email.php */
/* Location: ./system/expressionengine/third_party/cron_email/pi.cron_email.php */