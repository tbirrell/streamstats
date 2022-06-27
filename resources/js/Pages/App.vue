<template>
    <div>
        Stuff goes here
    </div>
</template>

<script>
import {Head} from '@inertiajs/inertia-vue3';
import {DateTime} from 'luxon';

export default {
    props: {
        totalStreamsByGame: Object,
        topGamesByViewer: Object,
        medianViewers: Number,
        top100Streams: Array,
        allStreams: Array,
    },
    data: () => ({
        userStreams: null
    }),
    computed: {
        streamsByStartTime() {
            var res = {};
            this.allStreams.forEach(function (stream) {
                let end = DateTime.now().setZone("utc");
                let start = DateTime.fromSQL(stream.start_time, { zone: "utc" });
                let diff = end.diff(start, ['hours', 'minutes']);

                let key = (diff.minutes >= 30) ? diff.hours + 1 : diff.hours;

                if (!res.hasOwnProperty(key)) {
                    res[key] = 1;
                } else {
                    res[key]++;
                }
            });
            return res;
        },
        followedTop1000() {
            return [];
        },
        lowestStreamNeeds() {
            return 101;
        },
        sharedTags() {
            return [];
        }
    },
    methods: {
        getUserStreams() {
            if (!this.userStreams) {
                //
            } else {
                return this.userStreams;
            }
        },
    }
};
</script>
