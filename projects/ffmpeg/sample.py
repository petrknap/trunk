from __init__ import *

FFmpeg('./ffmpeg.exe', './sample').run(
    Save(
        Concat(
            Cut(Open('./sample/input.mp4'), duration=3),
            Cut(Open('./sample/input.mp4'), start=3, duration=3),
            Cut(Open('./sample/input.mp4'), start=6),
        ),
        './sample/output.mp4'
    )
)
