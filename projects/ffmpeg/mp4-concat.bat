d:
cd \Users\DashC\Videos

set file=%~1

REM file is text file with lines: `file 'path'`

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -f concat -safe 0 -i "%file%" "%file%.concat.mp4"