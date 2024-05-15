--
-- Table structure for table `video_conference`
--

CREATE TABLE `video_conference` (
  `video_conference_id` int(11) NOT NULL,
  `vc_host_id` varchar(110) NOT NULL,
  `vc_room_id` varchar(50) NOT NULL,
  `vc_room_password` varchar(250) DEFAULT NULL,
  `vc_title` varchar(100) DEFAULT NULL,
  `vc_descrption` varchar(250) DEFAULT NULL,
  `vc_duration` smallint(6) NOT NULL,
  `vc_participant_count` smallint(6) NOT NULL,
  `vc_start_time` datetime NOT NULL,
  `vc_end_time` datetime NOT NULL,
  `vc_status` tinyint(4) NOT NULL,
  `api_response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zoom_access_token`
--

CREATE TABLE `zoom_access_token` (
  `access_token_id` int(11) NOT NULL,
  `access_token` text NOT NULL,
  `expiry_time` datetime NOT NULL,
  `api_response` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `video_conference`
--
ALTER TABLE `video_conference`
  ADD PRIMARY KEY (`video_conference_id`);

--
-- Indexes for table `zoom_access_token`
--
ALTER TABLE `zoom_access_token`
  ADD PRIMARY KEY (`access_token_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `video_conference`
--
ALTER TABLE `video_conference`
  MODIFY `video_conference_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zoom_access_token`
--
ALTER TABLE `zoom_access_token`
  MODIFY `access_token_id` int(11) NOT NULL AUTO_INCREMENT;