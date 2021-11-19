<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
Copyright (C) 2005 - 2021 Packet Tide, LLC

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
PACKET TIDE, LLC BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

Except as contained in this notice, the name of Packet Tide, LLC shall not be
used in advertising or otherwise to promote the sale, use or other dealings
in this Software without prior written authorization from Packet Tide, LLC.
*/

/**
 * Cron_email Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Packet Tide
 * @copyright		Copyright (C) 2005 - 2021 Packet Tide, LLC
 * @link			https://github.com/EllisLab/Cron-Email
 */

class Cron_email {

    public $return_data = '';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */
    function __construct()
    {
        $to		 = (ee()->TMPL->fetch_param('to') !== FALSE)   ? ee()->TMPL->fetch_param('to')   : '';
        $cc		 = (ee()->TMPL->fetch_param('cc') !== FALSE)   ? ee()->TMPL->fetch_param('cc')   : '';
        $bcc	 = (ee()->TMPL->fetch_param('bcc') !== FALSE)  ? ee()->TMPL->fetch_param('bcc')  : '';
        $from	 = (ee()->TMPL->fetch_param('from') !== FALSE) ? ee()->TMPL->fetch_param('from') : ee()->config->item('webmaster_email');
        $subject = (ee()->TMPL->fetch_param('subject') !== FALSE) ? ee()->TMPL->fetch_param('subject') : '';
        $message = ee()->TMPL->tagdata;

        if ($to == '' OR $subject == '' OR $message == '') return false;

        if (ee()->TMPL->fetch_param('parse_tag') == 'on' && stristr($message, '{'))
        {
        	$top	= '';
        	$bottom	= '';

        	if (preg_match("/".LD.'email_top'.RD."(.*?)".LD.SLASH.'email_top'.RD."/s", ee()->TMPL->tagdata, $matches))
        	{
        		$top = $matches['1'];
        		ee()->TMPL->tagdata = str_replace($matches['0'], '', ee()->TMPL->tagdata);
        	}

        	if (preg_match("/".LD.'email_bottom'.RD."(.*?)".LD.SLASH.'email_bottom'.RD."/s", ee()->TMPL->tagdata, $matches))
        	{
        		$bottom = $matches['1'];
        		ee()->TMPL->tagdata = str_replace($matches['0'], '', ee()->TMPL->tagdata);
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
        	$channel->query = ee()->db->query($channel->sql);

        	if ($channel->query->num_rows() == 0)
        	{
        	    return false;
        	}

			ee()->load->library('typography');

        	ee()->typography->encode_email = false;

        	ee()->TMPL->tagparams['rdf'] = 'off'; // Turn off RDF code

        	$channel->fetch_categories();
        	$channel->parse_channel_entries();
        	$message = $top.$channel->return_data.$bottom;
        }

		ee()->load->helper('text');
        $message = entities_to_ascii($message);

		ee()->load->library('email');

		ee()->email->wordwrap = FALSE;
        ee()->email->EE_initialize();
        ee()->email->to($to);
        ee()->email->cc($cc);
        ee()->email->bcc($bcc);
        ee()->email->from($from);
        ee()->email->subject($subject);
       	ee()->email->message($message);
		ee()->email->send();

		return TRUE;
	}
}
