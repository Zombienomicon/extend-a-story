<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002-2014 Jeffrey J. Weston and Matthew Duhan


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


For information about Extend-A-Story and its authors, please visit the website:
http://www.sir-toby.com/extend-a-story/

*/

function prepareParam( &$param )
{
    if ( get_magic_quotes_gpc() == 1 )
    {
        $param = stripslashes( $param );
    }

    $param = trim( $param );
}

function maximumWordLength( $input )
{
    $result = 0;

    $word = strtok( $input, " \t\n\r\0\x0B" );

    while( ! ( $word === false ))
    {
        if ( strlen( $word ) > $result )
        {
            $result = strlen( $word );
        }

        $word = strtok( " \t\n\r\0\x0B" );
    }

    return $result;
}

function displayError( $error, $fatal )
{
    echo( "<HTML><HEAD>" );
    echo( "<TITLE>Errors Detected</TITLE>" );
    echo( "</HEAD><BODY>" );

    echo( "<H1>Errors Detected</H1>" );

    if ( $fatal )
    {
        echo( "The following fatal errors have occurred. " );
        echo( "Please contact the site administrator." );
    }
    else
    {
        echo( "The following errors were detected with your submission. " );
        echo( "Please use your browser's back button, correct the errors, " );
        echo( "and try your submission again." );
    }

    echo( "<HR>" );
    echo( $error );

    exit;
}

function createEpisodeEditLog( &$error, &$fatal, $episode, $editLogEntry )
{
    // read the episode to log from the database
    $result = mysql_query( "SELECT SchemeID, ImageID, IsLinkable, IsExtendable, " .
                                  "AuthorMailto, AuthorNotify, Title, Text, AuthorName, " .
                                  "AuthorEmail " .
                             "FROM Episode WHERE EpisodeID = " . $episode );

    if ( ! $result )
    {
        $error .= "Unable to query original episode from database.<BR>";
        $fatal = true;
        return;
    }

    $row = mysql_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch original episode row from database.<BR>";
        $fatal = true;
        return;
    }

    $schemeID     = $row[ 0 ];
    $imageID      = $row[ 1 ];
    $isLinkable   = $row[ 2 ];
    $isExtendable = $row[ 3 ];
    $authorMailto = $row[ 4 ];
    $authorNotify = $row[ 5 ];
    $title        = $row[ 6 ];
    $text         = $row[ 7 ];
    $authorName   = $row[ 8 ];
    $authorEmail  = $row[ 9 ];

    // insert the episode edit log into the database
    $result = mysql_query( "INSERT " .
                             "INTO EpisodeEditLog " .
                                  "( " .
                                      "EpisodeID, " .
                                      "SchemeID, " .
                                      "ImageID, " .
                                      "IsLinkable, " .
                                      "IsExtendable, " .
                                      "AuthorMailto, " .
                                      "AuthorNotify, " .
                                      "Title, " .
                                      "Text, " .
                                      "AuthorName, " .
                                      "AuthorEmail, " .
                                      "EditDate, " .
                                      "EditLogEntry " .
                                  ") " .
                           "VALUES " .
                                  "( " .
                                            $episode                             .  ", " .
                                            $schemeID                            .  ", " .
                                            $imageID                             .  ", " .
                                      "'" . $isLinkable                          . "', " .
                                      "'" . $isExtendable                        . "', " .
                                      "'" . $authorMailto                        . "', " .
                                      "'" . $authorNotify                        . "', " .
                                      "'" . mysql_escape_string( $title        ) . "', " .
                                      "'" . mysql_escape_string( $text         ) . "', " .
                                      "'" . mysql_escape_string( $authorName   ) . "', " .
                                      "'" . mysql_escape_string( $authorEmail  ) . "', " .
                                      "'" . date( "n/j/Y g:i:s A" )              . "', " .
                                      "'" . mysql_escape_string( $editLogEntry ) . "' "  .
                                  ")" );

    if ( ! $result )
    {
        $error .= "Unable to insert the episode edit log into the database.<BR>";
        $fatal = true;
        return;
    }

    // get the new EpisodeEditLogID from the database
    $result = mysql_query( "SELECT LAST_INSERT_ID()" );

    if ( ! $result )
    {
        $error .= "Unable to query the new EpisodeEditLogID.<BR>";
        $fatal = true;
        return;
    }

    $row = mysql_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch the new EpisodeEditLogID row.<BR>";
        $fatal = true;
        return;
    }

    $nextEpisodeEditLogID = $row[ 0 ];

    // read the options to log from the database
    $result = mysql_query( "SELECT TargetEpisodeID, " .
                                  "IsBackLink, " .
                                  "Description " .
                             "FROM Link " .
                            "WHERE SourceEpisodeID = " . $episode . " " .
                            "ORDER BY LinkID" );

    if ( ! $result )
    {
        $error .= "Unable to query episode links from the database.<BR>";
        $fatal = true;
        return;
    }

    for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
    {
        $row = mysql_fetch_row( $result );
        createLinkEditLog( $error, $fatal, $nextEpisodeEditLogID,
                           $row[ 0 ], $row[ 1 ], $row[ 2 ] );
    }

    return $nextEpisodeEditLogID;
}

function createLinkEditLog( &$error, &$fatal, $episodeEditLogID, $targetEpisodeID, $isBackLink,
                            $description )
{
    // insert the link edit log into the database
    $result = mysql_query( "INSERT " .
                             "INTO LinkEditLog " .
                                  "( " .
                                      "EpisodeEditLogID, " .
                                      "TargetEpisodeID, " .
                                      "IsBackLink, " .
                                      "Description " .
                                  ") " .
                           "VALUES " .
                                  "( " .
                                            $episodeEditLogID                   .  ", " .
                                            $targetEpisodeID                    .  ", " .
                                      "'" . $isBackLink                         . "', " .
                                      "'" . mysql_escape_string( $description ) . "' " .
                                  ")" );

    if ( ! $result )
    {
        $error .= "Unable to insert the link edit log into the database.<BR>";
        $fatal = true;
        return;
    }

    // get the new LinkEditLogID from the database
    $result = mysql_query( "SELECT LAST_INSERT_ID()" );

    if ( ! $result )
    {
        $error .= "Unable to query the new LinkEditLogID.<BR>";
        $fatal = true;
        return;
    }

    $row = mysql_fetch_row( $result );

    if ( ! $row )
    {
        $error .= "Unable to fetch the new LinkEditLogID row.<BR>";
        $fatal = true;
        return;
    }

    return $row[ 0 ];
}

function extensionNotification( &$error, &$fatal, $email, $parent, $episode, $authorName )
{
    $storyName      = Util::getStringValue( "StoryName"      );
    $storyHome      = Util::getStringValue( "StoryHome"      );
    $readEpisodeURL = Util::getStringValue( "ReadEpisodeURL" );
    $adminEmail     = Util::getStringValue( "AdminEmail"     );

    $message = "This is an automated message.\n" .
               "\n" .
               "Episode " . $episode . ", a child of episode " . $parent .
               ", has been created.\n" .
               $readEpisodeURL . "?episode=" . $episode . "\n" .
               "\n" .
               "Author of the new episode: " . $authorName . "\n" .
               "\n" .
               "This email was automatically generated and sent because at some\n" .
               "point you created one or more episodes in the expandable story\n" .
               "          " . $storyName . "\n" .
               "     " . $storyHome . "\n" .
               "and asked to be notified when someone expanded your story line.";

    mail( $email, $storyName . " - Extension", $message,
          "From: " . $adminEmail, "-f" . $adminEmail );
}

function getEpisodeBodyTranslationTable()
{
    return array( "&lt;P&gt;"  => "<P>",
                  "&lt;p&gt;"  => "<p>",
                  "&lt;/P&gt;" => "</P>",
                  "&lt;/p&gt;" => "</p>",
                  "&lt;BR&gt;" => "<BR>",
                  "&lt;bR&gt;" => "<bR>",
                  "&lt;Br&gt;" => "<Br>",
                  "&lt;br&gt;" => "<br>",
                  "&lt;HR&gt;" => "<HR>",
                  "&lt;hR&gt;" => "<hR>",
                  "&lt;Hr&gt;" => "<Hr>",
                  "&lt;hr&gt;" => "<hr>",
                  "&lt;B&gt;"  => "<B>",
                  "&lt;b&gt;"  => "<b>",
                  "&lt;/B&gt;" => "</B>",
                  "&lt;/b&gt;" => "</b>",
                  "&lt;I&gt;"  => "<I>",
                  "&lt;i&gt;"  => "<i>",
                  "&lt;/I&gt;" => "</I>",
                  "&lt;/i&gt;" => "</i>" );
}

function getOptionTranslationTable()
{
    return array( "&lt;B&gt;"  => "<B>",
                  "&lt;b&gt;"  => "<b>",
                  "&lt;/B&gt;" => "</B>",
                  "&lt;/b&gt;" => "</b>",
                  "&lt;I&gt;"  => "<I>",
                  "&lt;i&gt;"  => "<i>",
                  "&lt;/I&gt;" => "</I>",
                  "&lt;/i&gt;" => "</i>" );
}

function getEmailAddressTranslationTable()
{
    return array( "\"" => "'",
                  "@"  => " at ",
                  "."  => " dot " );
}

function canEditEpisode( $sessionID, $userID, $episodeID )
{
    if ( $userID != 0 )
    {
        return true;
    }

    $result = mysql_query( "SELECT AuthorSessionID, CreationDate " .
                             "FROM Episode " .
                            "WHERE EpisodeID = " . $episodeID );

    if ( ! $result )
    {
        return false;
    }

    $row = mysql_fetch_row( $result );

    if ( ! $row )
    {
        return false;
    }

    $authorSessionID = $row[ 0 ];
    $creationDate    = $row[ 1 ];

    if ( $sessionID == $authorSessionID )
    {
        $maxEditDays = Util::getIntValue( "MaxEditDays" );

        $creationTime = strtotime( $creationDate );
        $curTime      = time();
        $seconds      = $curTime - $creationTime;
        $minutes      = (int) ( $seconds / 60 );
        $hours        = (int) ( $minutes / 60 );
        $days         = (int) ( $hours   / 24 );

        if ( $days < $maxEditDays )
        {
            return true;
        }
    }

    return false;
}

?>
