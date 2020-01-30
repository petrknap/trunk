from __init__ import *
from video_filters import *

FFmpeg('./ffmpeg.exe', './sample').run(
    Save(
        Concat(
            Save(
                Concat(
                    Cut(Open('./sample/input.mp4'), duration=3),
                    Cut(Open('./sample/input.mp4'), start=3, duration=3),
                    Cut(Open('./sample/input.mp4'), start=6),
                ),
                './sample/output - cut and concat.mp4'
            ),
            Save(
                LensCorrection(
                    Open('./sample/input.mp4'),
                    LensCorrection.GoPro
                ),
                './sample/output - lens correction.mp4'
            ),
            Save(
                VidStab(
                    Open('./sample/input.mp4'),
                    VidStab.default_parameters
                ),
                './sample/output - vid stab.mp4'
            )
        ),
        './sample/output.mp4'
    )
)
