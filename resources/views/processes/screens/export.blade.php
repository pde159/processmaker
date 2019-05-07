@extends('layouts.layout')

@section('title')
    {{__('Export Screen')}}
@endsection

@section('sidebar')
    @include('layouts.sidebar', ['sidebar'=> Menu::get('sidebar_processes')])
@endsection

@section('content')
    @include('shared.breadcrumbs', ['routes' => [
        __('Processes') => route('processes.index'),
        __('Screens') => route('screens.index'),
        __('Export') => null,
    ]])
    <div class="container" id="exportScreen">
        <div class="row">
            <div class="col">
                <div class="card text-center">
                    <div class="card-header bg-light" align="left">
                        <h5>{{__('Export Screen')}}</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{__('You are about to export a Screen.')}}</h5>
                        <p class="card-text">{{__('All the configurations of the screen will be exported.')}}</p>
                    </div>
                    <div class="card-footer bg-light" align="right">
                        <button type="button" class="btn btn-outline-secondary" @click="onCancel">
                            {{__('Cancel')}}
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" @click="onExport">
                            {{__('Download')}}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
      new Vue({
        el: '#exportScreen',
        data: {
          screenId: @json($screen->id)
        },
        methods: {
          onCancel() {
            window.location = '{{ route("screens.index") }}';
          },
          onExport() {
            ProcessMaker.apiClient.post('screens/' + this.screenId + '/export')
              .then(response => {
                window.location = response.data.url;
                ProcessMaker.alert('{{__('The screen was exported.')}}', 'success');
              })
              .catch(error => {
                ProcessMaker.alert(error.response.data.error, 'danger');
              });
          }
        }
      })
    </script>
@endsection
