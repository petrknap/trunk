from . import *


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


class Unsharp(Runner):
    default_parameters = {
        'lx': 5,
        'ly': 5,
        'la': 0.8,
        'cx': 3,
        'cy': 3,
        'ca': 0.4,
    }

    def __init__(self, previous_or_file, parameters):
        self.parameters = parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = [
                        '-vf', 'unsharp' +
                               '=' + str(self.parameters.get('lx')) +
                               ':' + str(self.parameters.get('ly')) +
                               ':' + str(self.parameters.get('la')) +
                               ':' + str(self.parameters.get('cx')) +
                               ':' + str(self.parameters.get('cy')) +
                               ':' + str(self.parameters.get('ca'))
                    ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file


class VidStab(Runner):
    default_parameters = {
        'shakiness': 3,
        'accuracy': 5,
        'stepsize': 7,
        'zoom': 1,
        'smoothing': 5,
    }

    def __init__(self, previous_or_file, parameters):
        self.parameters = parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        detect_output = ffmpeg.working_file('detected.trf')
        detect_arguments = [
                        '-vf', 'vidstabdetect' +
                               '=shakiness=' + str(self.parameters.get('shakiness')) +
                               ':accuracy=' + str(self.parameters.get('accuracy')) +
                               ':stepsize=' + str(self.parameters.get('stepsize')) +
                               ':result=' + detect_output,
                        '-f', 'null'
                    ]
        ffmpeg.execute(input_file, detect_arguments, '-')
        output_file = ffmpeg.working_file(input_file)
        arguments = [
                        '-vf', 'vidstabtransform' +
                               '=zoom=' + str(self.parameters.get('zoom')) +
                               ':smoothing=' + str(self.parameters.get('smoothing')) +
                               ':input=' + detect_output,
                    ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file
