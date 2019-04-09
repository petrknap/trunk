d:
cd \Users\DashC\Videos

set file=%~1

ffmpeg -i "%file%" -vf "[in] lenscorrection=cx=0.5:cy=0.5:k1=-0.227:k2=-0.022 [out]" -acodec copy "%file%.defished.jpg"