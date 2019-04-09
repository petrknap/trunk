d:
cd \Users\DashC\Videos

set file=%~1
set trf_file=%TIME%
set trf_file=%trf_file::=_%
set trf_file=%trf_file:,=_%
set trf_file=%trf_file%.trf

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -vf "[in] deshake=x=320:y=180:w=1280:h=720:rx=64:ry=64:edge=mirror [deshaked]; [deshaked] unsharp=5:5:0.8:3:3:0.4 [out]"  -acodec copy "%file%.deshaked.mp4"

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -vf "[in] deshake=x=320:y=180:w=1280:h=720:rx=64:ry=64:edge=mirror [deshaked]; [deshaked] vidstabdetect=shakiness=3:accuracy=5:stepsize=7:result=%trf_file% [out]" -f null -

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -vf "[in] deshake=x=320:y=180:w=1280:h=720:rx=64:ry=64:edge=mirror [deshaked]; [deshaked] vidstabtransform=zoom=1:smoothing=5:input=%trf_file% [vidstab]; [vidstab] unsharp=5:5:0.8:3:3:0.4 [out]" -acodec copy "%file%.deshaked_vidstab.mp4"

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -vf "[in] vidstabdetect=shakiness=3:accuracy=5:stepsize=7:result=%trf_file% [out]" -f null -

start /b powershell.exe -command "ping 127.0.0.1 -n 1; $progs = Get-Process -Name ffmpeg; Foreach ($prog in $progs) { $prog.PriorityClass = [System.Diagnostics.ProcessPriorityClass]::IDLE }"

ffmpeg -i "%file%" -vf "[in] vidstabtransform=zoom=1:smoothing=5:input=%trf_file% [vidstab]; [vidstab] unsharp=5:5:0.8:3:3:0.4 [out]" -acodec copy "%file%.vidstab.mp4"

del %trf_file%
