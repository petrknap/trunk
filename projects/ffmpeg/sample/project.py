import os
import sys

sys.path.append(os.path.abspath('../..'))

from ffmpeg import Concat, Cut, FFmpeg, Open, Save
from ffmpeg.video_filters import Deshake, LensCorrection, SetPts, Unsharp, VidStab

video = {
    'file': Open('./input.mp4'),
    'width': 620,
    'height': 360,
}
deshake_parameters = Deshake.default_parameters
deshake_parameters.update({
    'x': 40,
    'y': 40,
    'w': video.get('width') - 40 * 2,
    'h': video.get('height') - 40 * 2,
})

FFmpeg('../ffmpeg.exe', '.').run(
    Save(
        Concat(
            Save(
                Concat(
                    Cut(video.get('file'), duration=3),
                    Cut(video.get('file'), start=3, duration=3),
                    Cut(video.get('file'), start=6),
                ),
                './output - cut and concat.mp4'
            ),
            Save(
                Deshake(
                    video.get('file'),
                    deshake_parameters
                ),
                './output - deshake.mp4'
            ),
            Save(
                LensCorrection(
                    video.get('file'),
                    LensCorrection.GoPro
                ),
                './output - lens correction.mp4'
            ),
            Save(
                SetPts(
                    video.get('file'),
                    '1.25*PTS'
                ),
                './output - set pts.mp4'
            ),
            Save(
                Unsharp(
                    VidStab(
                        video.get('file'),
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
