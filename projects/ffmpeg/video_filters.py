from __init__ import *


class LensCorrection(Runner):
    GoPro = {
        'cx': 0.5,
        'cy': 0.5,
        'k1': -0.227,
        'k2': -0.022
    }

    def __init__(self, previous_or_file, parameters):
        self.parameters = parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = [
                        '-vf', 'lenscorrection' +
                               '=cx=' + str(self.parameters.get('cx')) +
                               ':cy=' + str(self.parameters.get('cy')) +
                               ':k1=' + str(self.parameters.get('k1')) +
                               ':k2=' + str(self.parameters.get('k2')),
                    ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file
