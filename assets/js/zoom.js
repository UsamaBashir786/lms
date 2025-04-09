        // Toggle mute/unmute
        function toggleMic(participantId) {
          const micIcon = document.getElementById(`micIcon${participantId}`);
          const video = document.getElementById(`video${participantId}`);
          if (micIcon.classList.contains("fa-microphone")) {
              micIcon.classList.replace("fa-microphone", "fa-microphone-slash");
              video.muted = true;
          } else {
              micIcon.classList.replace("fa-microphone-slash", "fa-microphone");
              video.muted = false;
          }
      }

      // Toggle video on/off
      function toggleVideo(participantId) {
          const videoIcon = document.getElementById(`videoIcon${participantId}`);
          const video = document.getElementById(`video${participantId}`);
          if (videoIcon.classList.contains("fa-video")) {
              videoIcon.classList.replace("fa-video", "fa-video-slash");
              video.style.display = 'none';
          } else {
              videoIcon.classList.replace("fa-video-slash", "fa-video");
              video.style.display = 'block';
          }
      }

      // Mute all participants
      document.getElementById("muteAllBtn").addEventListener("click", () => {
          toggleMic(1);
          toggleMic(2);
      });

      // Stop video for all participants
      document.getElementById("videoAllBtn").addEventListener("click", () => {
          toggleVideo(1);
          toggleVideo(2);
      });

      // End meeting functionality
      document.getElementById("endMeetingBtn").addEventListener("click", () => {
          if (confirm("Are you sure you want to end the meeting?")) {
              alert("Meeting Ended");
              // Add any further logic here to close the meeting or redirect
          }
      });

      // Toggle between participants' list
      document.getElementById("participantsBtn").addEventListener("click", () => {
          alert("Participants list will be shown.");
      });