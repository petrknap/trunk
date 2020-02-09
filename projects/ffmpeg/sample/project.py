import os
import sys

sys.path.append(os.path.abspath('../..'))

from ffmpeg import Concat, Cut, FFmpeg, Open, Save
from ffmpeg.filters import Deshake, LensCorrection, Tempo, Unsharp, VidStab

video = {
    'file': Open('./input.mp4'),
    'width': 620,
    'height': 360,
}

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
                    {}
                ),
                './output - deshake.mp4'
            ),
            Save(
                LensCorrection(
                    video.get('file'),
                    LensCorrection.GoProHeroHd
                ),
                './output - lens correction.mp4'
            ),
            Save(
                Tempo(
                    video.get('file'),
                    1.5
                ),
                './output - tempo.mp4'
            ),
            Save(
                Unsharp(
                    video.get('file'),
                    {}
                ),
                './output - unsharp.mp4'
            ),
            Save(
                VidStab(
                    video.get('file'),
                    {},
                    {}
                ),
                './output - vid stab.mp4'
            ),
        ),
        './output.mp4'
    )
)
