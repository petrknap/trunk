import subprocess
from os import path, remove
from shutil import copyfile
import uuid


class FFmpeg:
    encode_video = [
        '-vsync', 'vfr'
    ]
    copy_video = [
        '-vcodec', 'copy',
        '-vsync', 'vfr'
    ]
    copy_audio = [
        '-acodec', 'copy',
    ]

    def __init__(self, binary, working_directory):
        self.binary = binary
        self.working_directory = working_directory
        self.working_files = []

    def run(self, *args):
        for runner in args:
            runner.run(self)
        for working_file in self.working_files:
            if path.exists(working_file):
                remove(working_file)

    def working_file(self, input_file):
        (name, ext) = path.splitext(input_file)
        working_file = self.working_directory + path.sep + 'wip_' + uuid.uuid4().hex + ext
        self.working_files.append(working_file)
        return working_file

    def execute(self, input_file, arguments, output_file):
        command = [self.binary]
        if input_file:
            command += ['-i', input_file]
        command += arguments + [output_file]
        print(' '.join(command))
        process = subprocess.run(command)
        if process.returncode:
            raise Exception(' '.join(command))


class Runner:
    def __init__(self, previous_or_file):
        self.previous_or_file = previous_or_file

    def run(self, ffmpeg):
        if isinstance(self.previous_or_file, Runner):
            input_file = self.previous_or_file.run(ffmpeg)
        else:
            input_file = self.previous_or_file
        return self.do_run(ffmpeg, input_file)

    def do_run(self, ffmpeg, input_file):
        raise Exception('Not implemented')


class Open(Runner):
    def do_run(self, ffmpeg, input_file):
        return input_file


class Cut(Runner):
    def __init__(self, previous_or_file, **kwargs):
        self.start = kwargs.get('start')
        self.duration = kwargs.get('duration')
        super().__init__(previous_or_file)

    def do_run(self, ffmpeg, input_file):
        output_file = ffmpeg.working_file(input_file)
        arguments = []
        if self.start:
            arguments += ['-ss', str(self.start)]
        arguments += ['-i', input_file]  # input seeking (-i after -ss)
        if self.duration:
            arguments += ['-t', str(self.duration)]
        arguments += ffmpeg.copy_video + ffmpeg.copy_audio + ['-avoid_negative_ts', '1']
        ffmpeg.execute(None, arguments, output_file)
        return output_file


class Concat(Runner):
    def __init__(self, *args):
        self.runners = args
        super().__init__(None)

    def run(self, ffmpeg):
        concat_file = ffmpeg.working_file('concat.txt')
        last_file = None
        with open(concat_file, 'w') as file:
            for runner in self.runners:
                last_file = runner.run(ffmpeg)
                file.write('file \'%s\'\n' % path.abspath(last_file))
        output_file = ffmpeg.working_file(last_file)
        ffmpeg.execute(
            None,
            [
                '-f', 'concat',
                '-safe', '0',
                '-i', concat_file
            ] + ffmpeg.encode_video + ffmpeg.copy_audio,
            output_file
        )
        return output_file


class Save(Runner):
    def __init__(self, previous, output_file):
        self.output_file = output_file
        super().__init__(previous)

    def do_run(self, ffmpeg, input_file):
        copyfile(input_file, self.output_file)
        return self.output_file
