@echo off
start fiveserver // Start FiveServer
timeout /t 5 // Wait for the server to start (adjust time if necessary)
start firefox http://127.0.0.1:5500  // Replace with the actual URL for your FiveServer
exit
