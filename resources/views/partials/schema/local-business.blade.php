@if ($localBusiness = app(\App\Services\LocalSeo::class)->localBusiness())
<script type="application/ld+json">{!! \App\Services\LocalSeo::json($localBusiness) !!}</script>
@endif
