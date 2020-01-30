from __init__ import *
from video_filters import *

FFmpeg('./ffmpeg.exe', './sample').run(
    Save(
        Concat(
            LensCorrection(
                Cut(Open('./sample/input.mp4'), duration=3),
                LensCorrection.GoPro
            ),
            Cut(Open('./sample/input.mp4'), start=3, duration=3),
            Cut(Open('./sample/input.mp4'), start=6),
        ),
        './sample/output.mp4'
    )
)
