d:
cd \Users\DashC\Videos

set file=%~1

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -filter:v "lenscorrection=cx=0.5:cy=0.5:k1=-0.227:k2=-0.022, crop=w=1600:h=900, scale=1280:720" "%file%.yi.mp4"