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
        userStreams: Array,
        tags: Object
    },
    data: () => ({
        //
    }),
    computed: {
        streamsByStartTime() {
            var res = {};
            this.allStreams.forEach(function (stream) {
                let end = DateTime.now().setZone("utc");
                let start = DateTime.fromSQL(stream.start_time, {zone: "utc"});
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
            let followedTop1000 = [];

            this.userStreams.forEach(function (userStream) {
                let s = this.allStreams.find(function (topStream) {
                    return parseInt(topStream.stream_id) === parseInt(userStream.id);
                });
                console.log(s);
                if (typeof s !== 'undefined') {
                    followedTop1000.push(userStream);
                }
            }, this);

            return followedTop1000;
        },
        lowestStreamNeeds() {
            let userStream = this.userStreams.reduce(function (prev, curr) {
                return prev.viewer_count < curr.viewer_count ? prev : curr;
            });
            let top1000 = this.top100Streams.reduce(function (prev, curr) {
                return prev.viewer_count < curr.viewer_count ? prev : curr;
            });

            return top1000.viewer_count - userStream.viewer_count;
        },
        sharedTags() {
            let myTags = [];

            this.userStreams.forEach(function (userStream) {
                myTags = [...myTags, ...userStream.tag_ids];
            }, this);

            myTags = myTags.filter((value, index, self) => {
                return self.indexOf(value) === index;
            });

            let sharedTags = myTags.map((tag) => {
                return this.tags[tag];
            }).filter(Boolean);

            return sharedTags;
        }
    },
    methods: {}
};
</script>
