import os
import sys

sys.path.append(os.path.abspath('../..'))

from ffmpeg import *
from ffmpeg.video_filters import *

video_data = {
    'width': 620,
    'height': 360,
}
deshake_parameters = Deshake.default_parameters
deshake_parameters.update({
    'x': 40,
    'y': 40,
    'w': video_data.get('width') - 40 * 2,
    'h': video_data.get('height') - 40 * 2,
})

FFmpeg('../ffmpeg.exe', '.').run(
    Save(
        Concat(
            Save(
                Concat(
                    Cut(Open('input.mp4'), duration=3),
                    Cut(Open('input.mp4'), start=3, duration=3),
                    Cut(Open('input.mp4'), start=6),
                ),
                './output - cut and concat.mp4'
            ),
            Save(
                Deshake(
                    Open('input.mp4'),
                    deshake_parameters
                ),
                './output - deshake.mp4'
            ),
            Save(
                LensCorrection(
                    Open('input.mp4'),
                    LensCorrection.GoPro
                ),
                './output - lens correction.mp4'
            ),
            Save(
                Unsharp(
                    VidStab(
                        Open('input.mp4'),
                        VidStab.default_parameters
                    ),
                    Unsharp.default_parameters
                ),
                './output - vid stab and unsharp.mp4'
            )
        ),
        './output.mp4'
    )
)
