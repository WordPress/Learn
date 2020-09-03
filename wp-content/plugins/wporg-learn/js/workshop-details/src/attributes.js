export default {
    languageLabels: {
        type: 'array',
            default: [
    		{ label: 'English', value: 'en' },
    		{ label: 'French', value: 'fr' },
    		{ label: 'Korean', value: 'kr' },
    		{ label: 'Japan', value: 'jp' },
        ]
    },
    videoLanguage: {
        type: 'string',
        default: 'en'
    },
    captionLanguages: {
        type: 'array',
        default: [ ]
	}
}