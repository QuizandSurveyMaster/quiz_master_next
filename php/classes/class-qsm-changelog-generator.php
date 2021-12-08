<?php
/**
 * Changelog class
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class pings the GitHub API to load in the changelog for a particular milestone
 *
 * @since 4.7.0
 */
class QSM_Changelog_Generator {
    /**
     * Gets the changelog as an HTML list. Either echos or returns the list
     *
     * @since 4.7.0
     * @uses QSM_Changelog_Generator::get_changelog Used to retrieve the changelog from GitHub
     * @param string $repo The name of the GitHub repo. Should be similar to 'QuizandSurveyMaster/quiz_master_next'
     * @param int $milestone The number of the milestone in your GitHub repo
     * @param bool $echo Whether to echo or return the HTML list. Defaults to true
     * @return string The HTML list is returned if $echo is set to false
     */
    public static function get_changelog_list( $repo, $milestone, $echo = true ) {
        // Gets the changelog array
        $changelog = QSM_Changelog_Generator::get_changelog( $repo, $milestone );
        if ( $changelog && is_array( $changelog ) ) {
			// Creates header for milestone
			$display = "<h3>{$changelog["milestone"]["title"]}</h3>";

			// Creates paragraph for description
			if ( ! empty( $changelog["milestone"]["description"] ) ) {
				$display .= "<p>{$changelog["milestone"]["description"]}</p>";
			}

			// Creates paragraph for closed date
			$display .= "<p>Closed on {$changelog["milestone"]["closed_date"]}</p>";

			// Converts the issues array into HTML list
			$display .= '<ul class="changelog">';
			foreach ( $changelog["issues"] as $change ) {
				$label_type = $change["labels"][0]["name"];
				$display .= "<li class='fixed'><div class='two'>Closed</div>$label_type: {$change['title']} - <a target='_blank' href='{$change['url']}'>Issue #{$change['issue']}</a></li>";
			}
			$display .= '</ul>';

			// Echos or returns HTML list based on $echo parameter
			if ( true === $echo ) {
				echo wp_kses_post( $display );
			} else {
				return $display;
			}
        }
    }

    /**
     * Gets the changelog from GitHub and returns as an array
     *
     * @since 4.7.0
     * @uses QSM_Changelog_Generator::api_call Used to retrieve the changelog from GitHub
     * @param string $repo The name of the GitHub repo. Should be similar to 'QuizandSurveyMaster/quiz_master_next'
     * @param int $milestone The number of the milestone in your GitHub repo
     * @return array An array of all the titles of closed issues for the milestone
     */
    public static function get_changelog( $repo, $milestone ) {

		// Gets transient if available
		$changelog = get_transient( "changelog-$repo-$milestone" );
		if ( false === $changelog ) {

			$changelog = array();

			// Constructs url and then calls the api
			$issue_url = "https://api.github.com/repos/$repo/issues?milestone=$milestone&state=all";
			$issue_data = QSM_Changelog_Generator::api_call( $issue_url );

			if ( $issue_data ) {

			// Constructs url and then calls the api
			$milestone_url = "https://api.github.com/repos/$repo/milestones/$milestone";
			$milestone_data = QSM_Changelog_Generator::api_call( $milestone_url );

			if ( $milestone_data && isset( $milestone_data['title'] ) ) {
				$milestone_array = array(
					'title'       => $milestone_data["title"],
					'description' => $milestone_data["description"],
					'closed_date' => $milestone_data["closed_at"],
				);
			} else {
				$milestone_array = array(
					'title'       => '',
					'description' => '',
					'closed_date' => '',
				);
			}

			$changelog["milestone"] = $milestone_array;
			$changelog["issues"] = array();

			// Creates an array of all issues that are closed
			foreach ( $issue_data as $issue ) {
				if ( ! isset( $issue["pull_request"] ) ) {
				if ( "closed" === $issue["state"] ) {
					$changelog["issues"][] = array(
						'title'  => $issue["title"],
						'labels' => $issue["labels"],
						'issue'  => $issue["number"],
						'url'    => $issue["html_url"],
					);
				}
				}
			}
			}

			// Sets the transient
			set_transient( "changelog-$repo-$milestone", $changelog, 3600 );
		}
		return $changelog;
    }

    /**
     * Gets the contents of the $url that is passed
     *
     * @since 4.7.0
     * @param string $url The url to get the contents of
     * @return bool|array Returns false if encounters an error. Returns an associated array if successful
     */
    public static function api_call( $url ) {

		// Gets the url
		$response = wp_remote_get( $url );

		// If an error occurs, return false. If successful, return json_decoded array
		if ( is_wp_error( $response ) ) {
			return false;
		} else {
			$data = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $data ) ) {
			return false;
			} else {
			return json_decode( $data, true );
			}
		}
    }
}


