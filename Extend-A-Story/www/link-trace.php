<?php

/*

Extend-A-Story - Interactive, Extendable, Choose Your Own Adventure Story
Copyright (C) 2002  Jeff Weston


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

  require( "ExtendAStory.php" );

  $episode = $_GET[ "episode" ];

  $error = "";
  $fatal = false;

  $episode = ( int ) $episode;

  if ( $episode == 0 )
    $episode = 1;

  // Connect to the database.
  if ( empty( $error ) )
    connectToDatabase( $error, $fatal );

  if ( empty( $error ) )
    getSessionAndUserIDs( $error, $fatal, $sessionID, $userID );

  if ( empty( $error ) )
  {
    $storyName = getStringValue( $error, $fatal, "StoryName" );
    $siteName  = getStringValue( $error, $fatal, "SiteName"  );
    $storyHome = getStringValue( $error, $fatal, "StoryHome" );
    $siteHome  = getStringValue( $error, $fatal, "SiteHome"  );
  }

  if ( empty( $error ) )
  {
    $result = mysql_query( "select Link.SourceEpisodeID, " .
                                  "Episode.Title " .
                           "from Link, Episode " .
                           "where Link.SourceEpisodeID = Episode.EpisodeID " .
                             "and Link.TargetEpisodeID = " . $episode . " " .
                           "order by Episode.EpisodeID" );
    if ( ! $result )
    {
      $error .= "Problem retrieving the back link trace from the database.<BR>";
      $fatal = true;
    }
  }

  if ( ! empty( $error ) )
    displayError( $error, $fatal );

?>

<HTML><HEAD>
<TITLE><?php echo( $storyName ); ?>: Back Link Trace for Episode <?php echo( $episode ); ?></TITLE>
</HEAD><BODY>

<CENTER>
<H1><?php echo( $storyName ); ?>: Back Link Trace for Episode <?php echo( $episode ); ?></H1>

<TABLE>
  <TR>
    <TD>
      <OL>
<?php

  for ( $i = 0; $i < mysql_num_rows( $result ); $i++ )
  {
    $row = mysql_fetch_row( $result );
    $source = $row[ 0 ];
    $title  = $row[ 1 ];

    $title = htmlentities( $title );

?>
        <LI><A HREF="read.php?episode=<?php echo( $source ); ?>"><?php echo( $title ); ?></A></LI>
<?php

  }

?>
      </OL>
    </TD>
  </TR>
</TABLE>
<A HREF="read.php?episode=<?php echo( $episode ); ?>">Go Back</A>
<P>
<A HREF="<?php echo( $storyHome ); ?>"><?php echo( $storyName ); ?> Home</A>
<P>
<A HREF="<?php echo( $siteHome ); ?>"><?php echo( $siteName ); ?> Home</A>

</CENTER>

</BODY></HTML>
