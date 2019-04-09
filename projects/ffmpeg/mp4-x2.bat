d:
cd \Users\DashC\Videos

set file=%~1

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -r 60 -filter:v "setpts=0.5*PTS" -an "%file%.x2.mp4"