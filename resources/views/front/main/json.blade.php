<script type="application/ld+json">
[
   {
	"@context" : "http://schema.org",
	"@type" : "Organization",
	"name" : "ASEShop",
	"url" : "https://{{ env('DOMAIN_NAME') }}",
	"description" : "{!! trans('seo.homepage.description') !!}",
	"address" : {
		"@type" : "PostalAddress",
		"addressCountry" : "AZ",
		"addressLocality" : "AZ",
		"addressRegion" : "Baku",
		"postalCode" : "AZ1000",
		"streetAddress" : "{{ $setting->address }}"
	},
	"email" : "info@<?php echo env('DOMAIN_NAME')?>",
	"legalName": "ASEShop",
	"logo" : "{{ asset('uploads/setting/' . $setting->header_logo) }}",
	"sameAs": [
	   @if($setting->facebook) "{{ $setting->facebook }}", @endif
       @if($setting->facebook) "{{ $setting->instagram }}" @endif
      ],
      "brand" : [
		<?php $man_count = $stores->count() - 1;?>
      @foreach($stores as $key => $store)
            {
                "@type" : "Brand",
                "logo" : "{{ $store->logo }}",
			"name" : "{!! $store->name !!}",
			"description" : "{!! $store->name !!}-dən sifariş və Azərbaycana çatdırılma",
			"url" : "{{ $store->cashback_link }}"
		} @if($key != $man_count), @endif
		@endforeach
            ]
        },
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [{
                "@type": "ListItem",
                "position": 1,
                "name": "{!! __('front.menu.home') !!}",
	            "item": "{{ URL::current() }}"
	        }]
        }
   ]

</script>
