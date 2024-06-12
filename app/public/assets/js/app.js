/**
 * I encountered issues with MediaRecorder API so I used and modified this one https://codepen.io/eddch/pen/ZMOjPL
 */

(async () => {
    let leftchannel = [];
    let rightchannel = [];
    let recorder = null;
    let recording = false;
    let recordingLength = 0;
    let volume = null;
    let audioInput = null;
    let sampleRate = null;
    let AudioContext = window.AudioContext || window.webkitAudioContext;
    let context = null;
    let analyser = null;
    let stream = null;
    let tested = false;
    let audioBlob;
    let startBtn = document.querySelector('#record');
    let stopBtn = document.querySelector('#stop');
    let sendBtn = document.querySelector('#send');

    try {
        window.stream = stream = await getStream();
        console.log('Got stream');
    } catch(err) {
        alert('Issue getting mic', err);
    }

    const deviceInfos = await navigator.mediaDevices.enumerateDevices();

    var mics = [];
    for (let i = 0; i !== deviceInfos.length; ++i) {
        let deviceInfo = deviceInfos[i];
        if (deviceInfo.kind === 'audioinput') {
            mics.push(deviceInfo);
            let label = deviceInfo.label ||
                'Microphone ' + mics.length;
            console.log('Mic ', label + ' ' + deviceInfo.deviceId)
        }
    }

    function getStream(constraints) {
        if (!constraints) {
            constraints = { audio: true, video: false };
        }

        return navigator.mediaDevices.getUserMedia(constraints);
    }

    setUpRecording();

    function setUpRecording() {
        context = new AudioContext();
        sampleRate = context.sampleRate;

        // creates a gain node
        volume = context.createGain();

        // creates an audio node from teh microphone incoming stream
        audioInput = context.createMediaStreamSource(stream);

        // Create analyser
        analyser = context.createAnalyser();

        // connect audio input to the analyser
        audioInput.connect(analyser);

        let bufferSize = 2048;
        recorder = context.createScriptProcessor(bufferSize, 2, 2);

        analyser.connect(recorder);

        // finally connect the processor to the output
        recorder.connect(context.destination);

        recorder.onaudioprocess = function(e) {
            // Check
            if (!recording) return;
            // Do something with the data, i.e Convert this to WAV
            let left = e.inputBuffer.getChannelData(0);
            let right = e.inputBuffer.getChannelData(1);
            if (!tested) {
                tested = true;
                // if this reduces to 0 we are not getting any sound
                if ( !left.reduce((a, b) => a + b) ) {
                    alert("There seems to be an issue with your Mic");
                    // clean up;
                    stop();
                    stream.getTracks().forEach(function(track) {
                        track.stop();
                    });
                    context.close();
                }
            }
            // we clone the samples
            leftchannel.push(new Float32Array(left));
            rightchannel.push(new Float32Array(right));
            recordingLength += bufferSize;
        };
    };

    function mergeBuffers(channelBuffer, recordingLength) {
        let result = new Float32Array(recordingLength);
        let offset = 0;
        let lng = channelBuffer.length;
        for (let i = 0; i < lng; i++){
            let buffer = channelBuffer[i];
            result.set(buffer, offset);
            offset += buffer.length;
        }

        return result;
    }

    function interleave(leftChannel, rightChannel){
        let length = leftChannel.length + rightChannel.length;
        let result = new Float32Array(length);

        let inputIndex = 0;

        for (let index = 0; index < length; ){
            result[index++] = leftChannel[inputIndex];
            result[index++] = rightChannel[inputIndex];
            inputIndex++;
        }

        return result;
    }

    function writeUTFBytes(view, offset, string){
        let lng = string.length;
        for (let i = 0; i < lng; i++){
            view.setUint8(offset + i, string.charCodeAt(i));
        }
    }

    function start() {
        recording = true;
        startBtn.disabled = true;
        stopBtn.disabled = false;
        // reset the buffers for the new recording
        leftchannel.length = rightchannel.length = 0;
        recordingLength = 0;
        console.log('context: ', !!context);
        if (!context) setUpRecording();
    }

    function stop() {
        recording = false;

        startBtn.disabled = false;
        sendBtn.disabled = false;
        stopBtn.disabled = true;

        // we flat the left and right channels down
        let leftBuffer = mergeBuffers ( leftchannel, recordingLength );
        let rightBuffer = mergeBuffers ( rightchannel, recordingLength );
        // we interleave both channels together
        let interleaved = interleave ( leftBuffer, rightBuffer );

        // we create our wav file
        let buffer = new ArrayBuffer(44 + interleaved.length * 2);
        let view = new DataView(buffer);

        // RIFF chunk descriptor
        writeUTFBytes(view, 0, 'RIFF');
        view.setUint32(4, 44 + interleaved.length * 2, true);
        writeUTFBytes(view, 8, 'WAVE');
        // FMT sub-chunk
        writeUTFBytes(view, 12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true);
        // stereo (2 channels)
        view.setUint16(22, 2, true);
        view.setUint32(24, sampleRate, true);
        view.setUint32(28, sampleRate * 4, true);
        view.setUint16(32, 4, true);
        view.setUint16(34, 16, true);
        // data sub-chunk
        writeUTFBytes(view, 36, 'data');
        view.setUint32(40, interleaved.length * 2, true);

        // write the PCM samples
        let lng = interleaved.length;
        let index = 44;
        let volume = 1;
        for (let i = 0; i < lng; i++){
            view.setInt16(index, interleaved[i] * (0x7FFF * volume), true);
            index += 2;
        }

        // our final binary blob
        audioBlob = new Blob ( [ view ], { type : 'audio/wav' } );

        const $audio = document.querySelector('#audio');
        $audio.src = URL.createObjectURL(audioBlob);
    }

    function pause() {
        recording = false;
        context.suspend()
    }

    function resume() {
        recording = true;
        context.resume();
    }

    startBtn.onclick = (e) => {
        start();
    }

    stopBtn.onclick = (e) => {
        stop();
    }

    sendBtn.onclick = async (e) => {
        this.disabled = true;

        try {
            const formData = new FormData();

            // Append the audio blob under the key 'file'
            formData.append('audioFile', audioBlob, 'audio.wav');
            formData.append('_token', document.getElementById('token').value);

            const response = await fetch('/audio-to-text', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            // If you expect a JSON response
            const data = await response.json();

            // Do something with the response data
            if (data && data.success) {
                document.querySelector('#result').innerHTML += '<br>' + data.text;
            }

        } catch (error) {
            console.error('Error:', error);
        }
    }
})()