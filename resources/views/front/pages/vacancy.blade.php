@extends('front.layout')
@section('content')
    @include('front.sections.page-header')
    <div class="custom-container section-space100">
        @if (session('success'))
            <div class="alert alert-success"
                 role="alert">{{ session('success') }}</div>
        @endif
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 400px)); gap: 20px; place-items: center">
            @foreach($jobs as $job)
                <div style="background: #fff; padding: 44px 34px; width: 100%" class="box-shadow-esi job-card"
                     data-toggle="modal" data-target="#jobModal-{{ $loop->index }}">
                    <h4 style="margin-bottom: 56px; font-size: 22px; font-weight: 500; color: #000">{{ $job['name'] }}</h4>
                    <p style="font-weight: 500; font-size: 16px; color: #000">{{ $job['city'] }}</p>
                </div>
                <div class="modal fade " id="jobModal-{{ $loop->index }}" tabindex="-1" role="dialog"
                     aria-labelledby="jobModalLabel-{{ $loop->index }}" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div style="border: none" class="modal-content rounded-esi2 box-shadow-esi">
                            <div style="display: flex; align-items: center; width: 100%" class="modal-header">
                                <h5 style="color: #000; font-size: 20px; font-weight: 500; margin-right: auto"
                                    class="modal-title" id="jobModalLabel-{{ $loop->index }}">{{ $job['title'] }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form action="{{route('apply')}}" method="post" enctype="multipart/form-data">
                                <div class="modal-body">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                    <!-- Application Form Section -->
                                    <div class="application-form">
                                        <h4 style="color: #000; font-size: 20px; font-weight: 500" class="mb-3">
                                            Ərizənizi təqdim edin</h4>
                                        <p style="color: #000; font-weight: 400; font-size: 14px" class=" mb-4">Şəxsi
                                            məlumatlarınızı daxil edin. Tələb olunan fayl formatlarına diqqət edin.</p>


                                        <input type="hidden" name="vacancy_name" value="{{$job->name}}">
                                        <div style="margin-block: 20px; border: 1px dashed #15549A; background: #F4F6FA; border-radius: 12px; padding:18px; text-align: center ">
                                            <label for="cvFile"
                                                   style="color: #15549A; font-size: 14px; font-weight: 500">CV
                                                Fayl</label>
                                            <div class="custom-file">
                                                <input type="file" required name="file" class="custom-file-input" id="cvFile">
                                            </div>
                                            <small class="form-text text-muted">Sadece: PDF ve ya DOCX (Maks.
                                                2MB)</small>
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 20px">
                                            <input style="border-radius: 6px; outline: none; border: 1px solid #DEDEDE;padding: 18px 24px; width: 100%; font-size: 20px"
                                                   type="text" name="name" id="fullName" required placeholder="Adınız">
                                            <input style="border-radius: 6px; outline: none; border: 1px solid #DEDEDE;padding: 18px 24px; width: 100%; font-size: 20px"
                                                   type="text" name="surname" id="lastName" required placeholder="Soyadınız">
                                            <input style="border-radius: 6px; outline: none; border: 1px solid #DEDEDE;padding: 18px 24px; width: 100%; font-size: 20px"
                                                   type="tel" name="phone" id="phone" required placeholder="Telefon">
                                            <input style="border-radius: 6px; outline: none; border: 1px solid #DEDEDE;padding: 18px 24px; width: 100%; font-size: 20px"
                                                   type="email" name="email" id="email" required placeholder="E-mail">
                                        </div>

                                    </div>
                                </div>

                                <div style="display: flex; flex-wrap: wrap; gap: 4%; border: none !important;"
                                     class="modal-footer">
                                    <button style="background: #fff; border-radius: 12px; border: 1px solid #ddd; font-size: 18px; font-weight: bold; padding:12px ; color: #15549A; width: 45%"
                                            type="button" data-dismiss="modal">Close
                                    </button>
                                    <button style="background: #15549A; border-radius: 12px; border: none; font-size: 18px; font-weight: bold; padding:12px ; color: #fff; width: 45%"
                                            type="submit">Apply Now
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Add jQuery and Bootstrap JS if not already included -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        .job-card {
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .job-card:hover {
            transform: translateY(-5px);
        }
    </style>
@endsection

{{--@extends('front.layout')--}}
{{--@section('content')--}}
{{--    @include('front.sections.page-header')--}}
{{--    <div class="custom-container section-space100">--}}
{{--        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 400px)); gap: 20px; place-items: center">--}}
{{--            @foreach($jobs as $job)--}}
{{--                <div style="background: #fff; padding: 44px 34px; width: 100%" class="box-shadow-esi" >--}}
{{--                    <h4 style="margin-bottom: 56px; font-size: 22px; font-weight: 500; color: #000">{{ $job['title'] }}</h4>--}}
{{--                    <p style="font-weight: 500; font-size: 16px; color: #000">{{ $job['location'] }}</p>--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        </div>--}}
{{--    </div>--}}

{{--@endsection--}}


{{--///////////////////////////////////////////////////////////////////////////////////--}}
{{--@extends('front.layout')--}}
{{--@section('content')--}}
{{--    @include('front.sections.page-header')--}}
{{--    <div class="custom-container section-space100">--}}
{{--        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 400px)); gap: 20px; place-items: center">--}}
{{--            @foreach($jobs as $job)--}}
{{--                <div style="background: #fff; padding: 44px 34px; width: 100%" class="box-shadow-esi job-card" data-toggle="modal" data-target="#jobModal-{{ $loop->index }}">--}}
{{--                    <h4 style="margin-bottom: 56px; font-size: 22px; font-weight: 500; color: #000">{{ $job['title'] }}</h4>--}}
{{--                    <p style="font-weight: 500; font-size: 16px; color: #000">{{ $job['location'] }}</p>--}}
{{--                </div>--}}

{{--                <!-- Modal for each job -->--}}
{{--                <div class="modal fade" id="jobModal-{{ $loop->index }}" tabindex="-1" role="dialog" aria-labelledby="jobModalLabel-{{ $loop->index }}" aria-hidden="true">--}}
{{--                    <div class="modal-dialog modal-lg" role="document">--}}
{{--                        <div class="modal-content">--}}
{{--                            <div class="modal-header">--}}
{{--                                <h5 class="modal-title" id="jobModalLabel-{{ $loop->index }}">{{ $job['title'] }}</h5>--}}
{{--                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">--}}
{{--                                    <span aria-hidden="true">&times;</span>--}}
{{--                                </button>--}}
{{--                            </div>--}}
{{--                            <div class="modal-body">--}}
{{--                                <!-- Job Details Section -->--}}
{{--                                <div class="mb-4">--}}
{{--                                    <p><strong>Location:</strong> {{ $job['location'] }}</p>--}}
{{--                                    @if(isset($job['description']))--}}
{{--                                        <p><strong>Description:</strong> {{ $job['description'] }}</p>--}}
{{--                                    @endif--}}
{{--                                    @if(isset($job['requirements']))--}}
{{--                                        <p><strong>Requirements:</strong> {{ $job['requirements'] }}</p>--}}
{{--                                    @endif--}}
{{--                                </div>--}}

{{--                                <!-- Application Form Section -->--}}
{{--                                <div class="application-form">--}}
{{--                                    <h4 class="mb-3">Ərizənizi təqdim edin</h4>--}}
{{--                                    <p class="text-muted mb-4">Şəxsi məlumatlarınızı daxil edin. Tələb olunan fayl formatlarına diqqət edin.</p>--}}

{{--                                    <form>--}}
{{--                                        <div  >--}}
{{--                                            <label for="fullName">Ad</label>--}}
{{--                                            <input type="text" class="form-control" id="fullName" placeholder="Adınızı daxil edin">--}}
{{--                                        </div>--}}

{{--                                        <div  >--}}
{{--                                            <label for="lastName">Soyad</label>--}}
{{--                                            <input type="text" class="form-control" id="lastName" placeholder="Soyadınızı daxil edin">--}}
{{--                                        </div>--}}

{{--                                        <div  >--}}
{{--                                            <label for="phone">Telefon</label>--}}
{{--                                            <input type="tel" class="form-control" id="phone" placeholder="Telefon nömrənizi daxil edin">--}}
{{--                                        </div>--}}

{{--                                        <div  >--}}
{{--                                            <label for="email">Email</label>--}}
{{--                                            <input type="email" class="form-control" id="email" placeholder="Email ünvanınızı daxil edin">--}}
{{--                                        </div>--}}

{{--                                        <div  >--}}
{{--                                            <label for="cvFile">CV Fayl</label>--}}
{{--                                            <div class="custom-file">--}}
{{--                                                <input type="file" class="custom-file-input" id="cvFile">--}}
{{--                                                <label class="custom-file-label" for="cvFile">Fayl seçin...</label>--}}
{{--                                            </div>--}}
{{--                                            <small class="form-text text-muted">Qəbul olunan formatlar: PDF, DOC, DOCX (Maksimum 5MB)</small>--}}
{{--                                        </div>--}}

{{--                                        <div  >--}}
{{--                                            <label for="coverLetter">Ərizə Mətni (İstəyə bağlı)</label>--}}
{{--                                            <textarea class="form-control" id="coverLetter" rows="3" placeholder="Ərizə mətninizi yaza bilərsiniz"></textarea>--}}
{{--                                        </div>--}}
{{--                                    </form>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="modal-footer">--}}
{{--                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Bağla</button>--}}
{{--                                <button type="button" class="btn btn-primary">Göndər</button>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        </div>--}}
{{--    </div>--}}

{{--    <!-- Add jQuery and Bootstrap JS if not already included -->--}}
{{--    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>--}}

{{--    <!-- Script for file input label -->--}}
{{--    <script>--}}
{{--        $(document).ready(function() {--}}
{{--            $('.custom-file-input').on('change', function() {--}}
{{--                let fileName = $(this).val().split('\\').pop();--}}
{{--                $(this).next('.custom-file-label').addClass("selected").html(fileName);--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}

{{--    <style>--}}
{{--        .job-card {--}}
{{--            cursor: pointer;--}}
{{--            transition: transform 0.3s ease;--}}
{{--        }--}}
{{--        .job-card:hover {--}}
{{--            transform: translateY(-5px);--}}
{{--        }--}}
{{--        .application-form {--}}
{{--            background: #f8f9fa;--}}
{{--            padding: 20px;--}}
{{--            border-radius: 5px;--}}
{{--            margin-top: 30px;--}}
{{--        }--}}
{{--        .modal-lg {--}}
{{--            max-width: 800px;--}}
{{--        }--}}
{{--    </style>--}}
{{--@endsection--}}