from . import *


def serialize_filter_parameters(parameters, override={}):
    parameters.update(override)
    serialized = ""
    for parameter in parameters:
        if parameters.get(parameter):
            serialized += parameter + "=" + str(parameters.get(parameter)) + ":"
    return serialized[:-1]


class Deshake(Runner):
    def __init__(self, previous_or_file, parameters):
        self.parameters = parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = [
            '-vf', 'deshake=' + serialize_filter_parameters(self.parameters),
        ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file


class LensCorrection(Runner):
    GoProHeroHd = {
        'cx': 0.5,
        'cy': 0.5,
        'k1': -0.335,
        'k2': 0.097,
    }

    def __init__(self, previous_or_file, parameters):
        self.parameters = parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = [
            '-vf', 'lenscorrection=' + serialize_filter_parameters(self.parameters)
        ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file


class Tempo(Runner):
    def __init__(self, previous_or_file, tempo):
        self.tempo = tempo
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = [
            '-filter_complex', '[0:v]setpts=' + str(1 / self.tempo) + '*PTS[v];' +
                               '[0:a]atempo=' + str(self.tempo) + '[a]',
            '-map', '[v]',
            '-map', '[a]',
        ] + ffmpeg.encode_video + ffmpeg.encode_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file


class Unsharp(Runner):
    def __init__(self, previous_or_file, parameters):
        self.parameters = parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = [
            '-vf', 'unsharp=' + serialize_filter_parameters(self.parameters)
        ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file


class VidStab(Runner):
    def __init__(self, previous_or_file, detect_parameters, transform_parameters):
        self.detect_parameters = detect_parameters
        self.transform_parameters = transform_parameters
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        detect_output = ffmpeg.working_file('detected.trf').replace('\\', '/')
        detect_arguments = [
            '-vf', 'vidstabdetect=' + serialize_filter_parameters(self.detect_parameters, {
                'result': detect_output,
            }),
            '-f', 'null'
        ]
        ffmpeg.execute(input_file, detect_arguments, '-')
        output_file = ffmpeg.working_file(input_file)
        arguments = [
            '-vf', 'vidstabtransform=' + serialize_filter_parameters(
                self.transform_parameters, {
                    'input': detect_output,
                }
            ),
        ] + ffmpeg.encode_video + ffmpeg.copy_audio
        ffmpeg.execute(input_file, arguments, output_file)
        return output_file
